# Personas MINVU Ñuble

Sistema interno de administración para la gestión de funcionarios de la SEREMI del Ministerio de Vivienda y Urbanismo, Región de Ñuble. Lo usa quien administra: no tiene registro público.

## Requisitos

- PHP 8.4 o superior (el proyecto se probó en 8.4.0; Laravel 13 declara 8.4.1, por eso `composer.json` desactiva `platform-check`)
- Composer 2
- Node.js 20 y npm
- Extensiones habituales de Laravel: `pdo_sqlite` o `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`

## Instalación

En la carpeta del proyecto:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

En Windows PowerShell, si `copy` no está disponible:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

La base por defecto es SQLite. Cree el archivo si no existe y cargue el esquema con datos de demostración:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Abra `http://localhost:8000`.

### Acceso

| Cuenta | Correo | Contraseña |
| --- | --- | --- |
| Super administrador | `admin@minvu-nuble.cl` | `Minvu.Nuble.2026` |
| Administrador | `gestor@minvu-nuble.cl` | `Gestor.Nuble.2026` |

El super administrador opera todo, incluidos los mantenedores, las cuentas y el registro de acciones. El administrador opera funcionarios, organización y gestión, y no entra a los mantenedores.

Cambie esas contraseñas desde **Mi ficha** apenas entre por primera vez. El seeder vuelve a dejar estas claves si se ejecuta de nuevo `db:seed` sobre los mismos usuarios.

Para volver a cargar el ejemplo desde cero:

```bash
php artisan migrate:fresh --seed
```

## Uso

1. Ingrese con la cuenta de super administrador o de administrador.
2. El inicio muestra totales, gráficos y accesos rápidos.
3. La ficha de cada funcionario concentra cursos, licencias, cargas familiares, contratos, permisos y calificaciones.
4. Departamentos y secciones tienen jefe, subrogante y analistas.
5. Los mantenedores (tipos de licencia, permiso, documento, usuarios y registro de acciones) los opera el super administrador.
6. Cada ingreso, intento rechazado, consulta, cambio y descarga queda en **Registro de acciones**.
7. El menú de la esquina superior derecha abre la ficha de la cuenta, el cambio de contraseña y el cierre de sesión.
8. El botón de sol o luna guarda el tema en este navegador.

En pantallas chicas el menú lateral se abre con el botón de la esquina superior izquierda.

## Archivos

Las fotografías y documentos se guardan en `storage/app/private`. En la base de datos queda solo la ruta. Se descargan por una ruta autenticada (`/archivos`); no quedan publicados en `public`.

Formatos aceptados: PDF, JPG, PNG y WebP. Las fotos pesan hasta 4 MB y los documentos hasta 8 MB.

## MySQL

En `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personas_minvu_nuble
DB_USERNAME=root
DB_PASSWORD=
```

Cree la base y ejecute `php artisan migrate --seed`.

## Producción

- `APP_ENV=production` y `APP_DEBUG=false`
- `APP_URL` con el dominio real, idealmente HTTPS
- `php artisan config:cache` y `php artisan route:cache` después de configurar el entorno
- `npm run build` en cada despliegue que cambie vistas o estilos
- Permisos de escritura en `storage` y `bootstrap/cache`
- Cambie la contraseña inicial y no deje el seeder de demostración como única medida de acceso

## Documentación

- [Guía para seguir construyendo con el mismo criterio](docs/ai-guidelines.md)
- [Arquitectura](docs/architecture.md)
- [Modelo de datos](docs/data-model.md)
- [Cómo agregar un módulo](docs/modules.md)

Las reglas que Cursor aplica en este repositorio están en `.cursor/rules/minvu-admin.mdc` y resumen ese mismo criterio.
