# Reglas para seguir construyendo este sistema

Estas reglas fijan el diseño y el orden del panel de Personas MINVU Ñuble. Cualquier módulo nuevo, hecho por una persona o por un asistente de código, debe respetarlas.

## Idioma

- Código, tablas, columnas, clases, métodos y rutas en inglés.
- Textos de la interfaz, validaciones visibles y mensajes flash en español.
- No mezclar nombres en español dentro de la base de datos (`id_funcionario`, `fecha_inicio`).

## Dónde va cada cosa

| Pieza | Lugar |
| --- | --- |
| Pantallas | `resources/views/{modulo}` |
| Controlador | `app/Http/Controllers`, delgado |
| Validación | `app/Http/Requests` |
| Modelo | `app/Models` |
| Valores cerrados | `app/Enums`, con `label()` y el trait `HasOptions` |
| Archivos subidos | `App\Support\StoredUpload`, disco `local` |
| Menú | `App\Support\Navigation` |
| Migración | `database/migrations`, una por cambio de esquema |

No agregue lógica de negocio a las vistas. No deje consultas Eloquent largas dentro de Blade.

## Controladores

Un controlador de recurso hace solo esto: filtrar el listado, mostrar el formulario, validar mediante un Form Request, guardar y redirigir con un mensaje en español.

La subida de archivos se resuelve con `StoredUpload::apply()` o `StoredUpload::store()`. El modelo declara las columnas de archivo en `storedFiles()` y usa el trait `DeletesStoredFiles`.

Si el registro pertenece a un funcionario y el formulario viene desde su ficha, conserve `context_employee_id` y redirija con `RedirectsToEmployeeContext`.

## Datos de un funcionario

La ficha (`employees.show`) es el lugar para ver y agregar lo que depende de una persona. El menú lateral igual tiene el listado general de esa entidad, con filtro por funcionario.

No cree pantallas paralelas para la misma información.

## Interfaz

- Use los componentes existentes: `x-page-header`, `x-card`, `x-button`, `x-badge`, `x-stat`, `x-avatar`, `x-delete`, `x-file-link`, `x-empty`, `x-record-section` y `x-form.*`.
- Colores institucionales: azul `#0b4f9c` (`brand-600`) y rojo `#d0121a` (`accent-500`). No introduzca otra paleta ni gradientes violeta.
- Tema claro y oscuro con las variantes `dark:` de Tailwind. El modo oscuro se activa con la clase `dark` en `<html>`, no con `prefers-color-scheme`.
- Toda pantalla nueva debe poder usarse en celular, en un escritorio y en un monitor ancho: una columna en móvil, grillas desde `sm` o `lg`, tablas dentro de `overflow-x-auto` y columnas secundarias ocultas con `hidden md:table-cell`.
- El encabezado ya trae el logo, el tema y el menú del administrador. No lo duplique.
- El logo vive en `public/images/logo-seremi.jpg`. En el encabezado se recorta en móvil y se muestra completo desde `sm`.
- Botón principal azul, secundario con borde, destructivo rojo. Confirmar el borrado con `x-delete`.
- Formularios con `@csrf`, `enctype="multipart/form-data"` si hay archivos, y `<x-form.errors />`.

## Validación y archivos

- RUT chileno con `App\Rules\ChileanRut` y normalización en `App\Support\Rut` antes de validar. Se guarda como `12345678-5`.
- Fechas de término iguales o posteriores al inicio.
- Llaves foráneas con `exists`.
- Jefe y subrogante no pueden ser la misma persona, ni repetirse en la lista de analistas.
- Imágenes: `jpg,jpeg,png,webp`, máximo 4096 KB. Documentos: esos mismos más `pdf`, máximo 8192 KB.
- Nunca guarde el binario en la base. Guarde la ruta relativa y sirva el archivo solo con el usuario autenticado.

## Base de datos

- Claves foráneas explícitas.
- Borrado de catálogos en uso: impedirlo en el controlador y `restrictOnDelete`.
- Borrado de un funcionario: eliminar en Eloquent los registros hijos para que también se borren sus archivos.
- Departamento o sección eliminados: las asignaciones quedan nulas (`nullOnDelete`), las personas no se borran.
- Valores de listas cerradas como `string`, respaldados por un enum PHP. No use enums nativos de MySQL.

## Qué no hacer

- No abrir registro público ni recuperación de clave por correo. Las cuentas las crea el super administrador.
- Hay dos roles: `super_admin` y `admin`. El administrador no entra a los mantenedores. Esas rutas van en el grupo `superadmin`.
- No anote a mano cada pantalla en el registro de acciones. `RecordActivity` y los eventos de acceso ya lo hacen. Si agrega un recurso, sume su nombre en español en `App\Support\ActivityLogger` para que la frase quede clara. Nunca guarde contraseñas en `activity_logs`.
- No agregar un framework de frontend distinto de Blade, Tailwind y Alpine.
- No servir documentos personales desde `public` o el disco `public`.
- No copiar estilos inline ni crear una hoja CSS paralela para un módulo.
- No usar `$guarded = []`.
- No poner controladores genéricos mágicos que oculten el recurso. La repetición corta y legible es preferible.

El detalle de carpetas y del modelo está en `docs/architecture.md`, `docs/data-model.md` y `docs/modules.md`.
