# Arquitectura

Aplicación Laravel 13 con Blade, Tailwind CSS 4 y Alpine.js. Entran dos tipos de cuenta, ambas de administración: el super administrador y el administrador. No hay API pública ni registro abierto.

## Capas

```
routes/web.php
  → Form Request (autorización básica + reglas)
    → Controlador (consulta, persistencia, redirección)
      → Modelo Eloquent
        → SQLite o MySQL
```

Los archivos subidos pasan por `App\Support\StoredUpload` y quedan en el disco `local` (`storage/app/private`). `FileController` los entrega solo si la ruta empieza por una carpeta permitida y el usuario está autenticado.

## Autenticación

Sesión de Laravel, con regeneración del identificador al entrar y al cambiar la contraseña. El ingreso se bloquea tras 5 intentos fallidos en un minuto por correo e IP. No existe ruta de registro. Las rutas de gestión usan el middleware `auth`; el login usa `guest`.

`users.role` vale `super_admin` o `admin`. El administrador usa funcionarios, organización y gestión. Los mantenedores —catálogos, usuarios y el registro de acciones— van en el grupo `superadmin` (`EnsureSuperAdmin`). El menú oculta ese grupo y un acceso directo vuelve al inicio con un aviso.

`RecordActivity` anota cada consulta, cambio, descarga y acceso sin sesión. El ingreso, el rechazo y el cierre de sesión se anotan con los eventos de autenticación. La contraseña no se guarda. El resultado queda en `activity_logs`, visible solo para el super administrador. Un módulo nuevo con rutas de recurso recibe una frase en español si su nombre está en `App\Support\ActivityLogger`; si no, igual queda registrado.

## Organización de la interfaz

El layout `resources/views/layouts/app.blade.php` arma el encabezado (logo, tema, usuario) y el contenido. El menú sale de `App\Support\Navigation` y se pinta en `layouts/navigation.blade.php`. En pantallas pequeñas es un panel fijo; desde `lg` queda fijo a la izquierda.

La ficha del funcionario (`employees/show`) es el centro de la gestión personal. Cada bloque enlaza al formulario de alta con `employee_id` y, al guardar o eliminar, puede volver a la ficha mediante `context_employee_id`.

## Extensión

Un módulo nuevo es un recurso con migración, modelo, Form Request, controlador, vistas y una entrada en `Navigation`. El paso a paso está en `docs/modules.md`.

La diferencia entre las dos cuentas es de sección, no de cada registro: la resuelve el middleware `superadmin`. Los controladores siguen delgados. Si más adelante una regla depende del registro, la policy va en `app/Policies`.
