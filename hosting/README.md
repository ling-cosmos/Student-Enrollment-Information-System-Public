# Original SEIS app on Vercel

This deployment runs the original PHP pages, forms, reports and handlers. The optional hosting adapter uses PostgreSQL for six-hour visitor sandboxes and serverless session storage. The normal MySQL/XAMPP path is retained when `PORTFOLIO_DEMO` is absent.

Configure server-only `DATABASE_URL` and `PORTFOLIO_DEMO=true`. Use a dedicated demo database and role with permission to create schemas; never connect it to a real student database. Each visitor gets an isolated schema seeded only from `hosting/schema.sql`. Expired sandbox schemas are removed when later visitors open the app. Photo uploads are disabled. All credentials remain in Vercel environment variables.

Prepare static files before CLI deployment:

```sh
mkdir -p public/assets
cp -r assets/css assets/js assets/img public/assets/
vercel deploy --prod
```

`api/index.php` is the only PHP entry point published by the deployment. It permits the original app routes and denies configuration, SQL exports, and arbitrary file paths. The explicit `/` rewrite is required: the wildcard rewrite alone does not match a bare slash. The pinned `vercel-php@0.9.0` community runtime includes PDO PostgreSQL and uses a Node 24 launcher selected through `package.json`. It is not an official Vercel runtime. Both upload and function-bundle exclusions deny local environment files, Git metadata, database exports, and downloaded test packages.

For local checks, enable the PHP PDO PostgreSQL extension and run `PORTFOLIO_DEMO=true DATABASE_URL=... php -S 127.0.0.1:4185 api/index.php`. A local PostgreSQL sandbox has verified dashboard rendering and student registration. Public database verification remains pending the owner's Neon marketplace acceptance. Do not link the hosted app as ready before checking registration, enrollment, reports, session persistence and visitor isolation on its public URL.
