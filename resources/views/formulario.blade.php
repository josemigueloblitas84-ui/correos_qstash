<form method="POST" action="/form/submit" enctype="multipart/form-data">
    @csrf
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="email" name="email" placeholder="Correo" required>
    <textarea name="mensaje" placeholder="Mensaje" required></textarea>
    <input type="file" name="adjuntos[]" multiple>
    <button type="submit">Enviar</button>
</form>

