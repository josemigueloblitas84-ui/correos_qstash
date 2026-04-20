<style>
    :root {
        --primary: {{ config('larecipe.ui.colors.primary') }};
        --secondary: {{ config('larecipe.ui.colors.secondary') }};
    }

    :not(pre)>code[class*=language-], pre[class*=language-] {
        border-top: 3px solid {{ config('larecipe.ui.colors.primary') }};
    }

    .bg-gradient-primary {
        background: linear-gradient(87deg, {{ config('larecipe.ui.colors.primary') }} 0, {{ config('larecipe.ui.colors.secondary') }} 100%) !important;
    }

    [v-cloak] > * {
        display: none;
    }

    [v-cloak]::before {
        content: " ";
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: #F2F6FA;
    }

    .documentation {
        background:
            radial-gradient(circle at top right, rgba(31, 120, 173, 0.08), transparent 25%),
            linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .documentation h1:first-of-type {
        color: #0f3557;
        border-left-width: 4px;
        padding-left: 1rem;
    }

    .documentation h1:first-of-type + p {
        font-size: 1.08rem;
        color: #5f6f7f;
        max-width: 900px;
        margin-bottom: 1.25rem;
    }

    .documentation h2,
    .documentation h3 {
        position: relative;
        color: #143d67;
        letter-spacing: -0.01em;
    }

    .documentation h2 {
        padding-left: 2.65rem;
    }

    .documentation h2::before {
        content: "\f0a9";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 0.1rem;
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #1f78ad 0%, #0f4c75 100%);
        box-shadow: 0 10px 22px rgba(15, 76, 117, 0.18);
        font-size: 0.88rem;
    }

    .documentation h3 {
        padding-left: 2.1rem;
        color: #1d4f7b;
    }

    .documentation h3::before {
        content: "\f105";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 0.15rem;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #1f78ad;
        background: rgba(31, 120, 173, 0.12);
        font-size: 0.82rem;
    }

    .documentation img {
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(15, 76, 117, 0.14);
        border: 1px solid rgba(31, 120, 173, 0.12);
    }

    .documentation > ul.page-toc {
        position: fixed;
        top: 110px;
        right: 24px;
        width: 20%;
        min-width: 240px;
        max-width: 320px;
        margin: 0;
        padding: 1rem;
        list-style: none;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(31, 120, 173, 0.12);
        box-shadow: 0 14px 34px rgba(15, 76, 117, 0.12);
        backdrop-filter: blur(8px);
        z-index: 5;
    }

    .documentation > ul.page-toc > li {
        list-style: none;
        margin: 0;
        padding: 0.65rem 0;
        border-bottom: 1px dashed #d6e3ee;
    }

    .documentation > ul.page-toc > li:last-child {
        border-bottom: 0;
    }

    .documentation > ul.page-toc > li.toc-title {
        padding-top: 0;
        color: #143d67;
        font-weight: 700;
        font-size: 0.92rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .documentation > ul.page-toc > li.toc-title::before {
        content: "\f0ca";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        margin-right: 0.5rem;
        color: #1f78ad;
    }

    .documentation > ul.page-toc a {
        color: #53687a;
        text-decoration: none;
        line-height: 1.5;
        transition: color 0.15s ease, transform 0.15s ease;
        display: inline-block;
    }

    .documentation > ul.page-toc a:hover {
        color: #1f78ad;
        transform: translateX(2px);
    }

    .documentation > ul.page-toc > li > a {
        font-size: 0.98rem;
    }

    .documentation > ul.page-toc > li > ul {
        list-style: none;
        margin: 0.55rem 0 0 0;
        padding-left: 0.85rem;
    }

    .documentation > ul.page-toc > li > ul > li {
        padding: 0.35rem 0 0.2rem;
        list-style: none;
        border-bottom: 0;
    }

    .documentation .page-toc li {
        padding-left: 0;
    }

    .documentation .page-toc li::before {
        content: none !important;
        display: none !important;
    }

    .documentation > ul.page-toc > li > ul > li > a {
        font-size: 0.9rem;
        color: #7890a3;
    }

    .documentation > ul:first-of-type:not(.page-toc) {
        position: static;
        top: auto;
        right: auto;
        width: auto;
        padding: 0;
        margin: 1rem 0;
        list-style: none;
    }

    .documentation ul:not(.page-toc),
    .documentation ol {
        margin: 1rem 0;
        padding-left: 0;
    }

    .documentation ul:not(.page-toc) > li,
    .documentation ol > li {
        position: relative;
        list-style: none;
        margin-bottom: 0.45rem;
        color: #3d4852;
        line-height: 1.72;
        padding: 0.3rem 0 0.3rem 2rem;
    }

    .documentation ul:not(.page-toc) > li::before {
        content: "\f058";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 0.42rem;
        color: #1f78ad;
        font-size: 0.95rem;
    }

    .documentation ol {
        counter-reset: docs-counter;
    }

    .documentation ol > li::before {
        counter-increment: docs-counter;
        content: counter(docs-counter);
        position: absolute;
        left: 0;
        top: 0.25rem;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(31, 120, 173, 0.14);
        color: #0f4c75;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .documentation ul:not(.page-toc) ul,
    .documentation ol ul,
    .documentation ol ol,
    .documentation ul:not(.page-toc) ol {
        margin-top: 0.45rem;
        padding-left: 0.5rem;
    }

    .documentation p {
        color: #455565;
    }

    .documentation blockquote {
        border-left: 4px solid #1f78ad;
        background: linear-gradient(180deg, rgba(31, 120, 173, 0.06), rgba(31, 120, 173, 0.02));
        border-radius: 14px;
        padding: 1rem 1.15rem;
        margin: 1.25rem 0;
    }

    .documentation blockquote p {
        color: #25435f;
    }

    @media (max-width: 1280px) {
        .documentation > ul.page-toc {
            width: 18rem;
        }
    }

    @media (max-width: 1080px) {
        .documentation > ul.page-toc {
            position: static;
            width: auto;
            min-width: 0;
            max-width: none;
            margin: 0 0 1.5rem 0;
        }
    }
    .documentation .page-toc a.is-current {
    color: #1f78ad;
    font-weight: 700;
    position: relative;
    }

    .documentation .page-toc a.is-current::before {
        content: "\f061";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        left: -1.1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #1f78ad;
        animation: toc-breathe 2.4s ease-in-out infinite;
    }

    @keyframes toc-breathe {
        0% {
            opacity: 0.55;
            transform: translateY(-50%) scale(0.92);
        }
        50% {
            opacity: 1;
            transform: translateY(-50%) scale(1.08);
        }
        100% {
            opacity: 0.55;
            transform: translateY(-50%) scale(0.92);
        }
    }

</style>
