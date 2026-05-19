<?php

namespace App\Services\Agenda;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Support\ActivityLogger;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Domain;

class InstitucionService
{
    public function getAllForDataTable()
    {
        return DB::table('tenants')
            ->leftJoin('domains', 'tenants.id', '=', 'domains.tenant_id')
            ->whereNull('tenants.deleted_at')
            ->select(
                'tenants.id',
                'tenants.nombre',
                'tenants.created_at',
                DB::raw("GROUP_CONCAT(domains.domain ORDER BY domains.domain SEPARATOR '||') as dominios")
            )
            ->groupBy('tenants.id', 'tenants.nombre', 'tenants.created_at')
            ->orderBy('tenants.created_at', 'desc');
    }

    public function store(array $data): Tenant
    {
        $tenant = Tenant::create([
            'id' => $data['id'],
            'nombre' => $data['nombre'],
        ]);

        $tenant->domains()->create([
            'domain' => $data['domain'],
        ]);

        ActivityLogger::log(
            'Institucion tenant creada',
            [
                'attributes' => [
                    'id' => $tenant->id,
                    'nombre' => $tenant->nombre,
                    'domain' => $data['domain'],
                ],
            ],
            null,
            null,
            'instituciones',
            'created'
        );

        return $tenant;
    }

    public function findById(string $id): ?Tenant
    {
        return Tenant::query()
            ->with('domains')
            ->whereKey($id)
            ->first();
    }

    public function update(string $id, array $data): bool
    {
        $tenant = $this->findById($id);

        if (! $tenant) {
            return false;
        }

        $old = [
            'id' => $tenant->id,
            'nombre' => $tenant->nombre,
            'domain' => $tenant->domains->first()?->domain,
        ];

        $tenant->update([
            'nombre' => $data['nombre'],
        ]);

        $domain = $tenant->domains()->orderBy('id')->first();

        if ($domain) {
            $domain->update([
                'domain' => $data['domain'],
            ]);
        } else {
            $tenant->domains()->create([
                'domain' => $data['domain'],
            ]);
        }

        ActivityLogger::log(
            'Institucion tenant actualizada',
            [
                'old' => $old,
                'attributes' => [
                    'id' => $tenant->id,
                    'nombre' => $data['nombre'],
                    'domain' => $data['domain'],
                ],
            ],
            null,
            null,
            'instituciones',
            'updated'
        );

        return true;
    }

    public function createAdministrator(string $id, array $data): ?array
    {
        $tenant = $this->findById($id);

        if (! $tenant) {
            return null;
        }

        $administrator = $tenant->run(function () use ($data) {
            app(RolesAndPermissionsSeeder::class)->run();

            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->email_verified_at = now();
            $user->estado = 1;
            $user->save();

            $user->assignRole('SuperAdministrador');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        ActivityLogger::log(
            'Administrador tenant creado',
            [
                'attributes' => [
                    'tenant_id' => $tenant->id,
                    'tenant_nombre' => $tenant->nombre,
                    'admin_id' => $administrator['id'],
                    'admin_name' => $administrator['name'],
                    'admin_email' => $administrator['email'],
                ],
            ],
            null,
            null,
            'instituciones',
            'admin_created'
        );

        return $administrator;
    }

    public function destroy(string $id): bool
    {
        $tenant = $this->findById($id);

        if (! $tenant) {
            return false;
        }

        $old = [
            'id' => $tenant->id,
            'nombre' => $tenant->nombre,
            'domains' => $tenant->domains->pluck('domain')->values()->all(),
        ];

        $deleted = (bool) $tenant->delete();

        if ($deleted) {
            ActivityLogger::log(
                'Institucion tenant dada de baja',
                [
                    'old' => $old,
                ],
                null,
                null,
                'instituciones',
                'deactivated'
            );
        }

        return $deleted;
    }

    public function primaryDomain(Tenant $tenant): ?string
    {
        return $tenant->domains->first()?->domain
            ?? Domain::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->value('domain');
    }
}
