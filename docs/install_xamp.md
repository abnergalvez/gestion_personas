# Instalar en XAMPP para Windows

Apache de XAMPP sirve el sistema al arrancar. No hace falta `php artisan serve`. La carpeta publicada es `public`, no la raíz del proyecto.

Use **XAMPP 8.4.28 para Windows de 64 bits**, desde [apachefriends.org/download](https://www.apachefriends.org/download.html). Trae PHP 8.4.28 y MariaDB 10.11.19. MariaDB se configura como MySQL. El instalador 8.3 se queda corto y el 8.5 no es el que se usó para armar este sistema.

XAMPP no incluye Composer ni Node. En ese PC también hacen falta Composer 2 y Node.js 20.

## 1. Que Apache y MySQL partan con XAMPP

En el Panel de control de XAMPP, abra **Config** (arriba a la derecha) y entre en **Autostart of modules**. Marque **Apache** y **MySQL**. Al abrir XAMPP, ambos quedan en verde.

Si además quiere que partan al encender Windows, en la columna **Service** marque la casilla de Apache y la de MySQL. Windows pide permisos de administrador e instala los dos servicios.

## 2. Decirle a Apache dónde está el sistema

Abra `C:\xampp\apache\conf\httpd.conf` y confirme que esta línea no tenga `#` al inicio:

```apache
Include conf/extra/httpd-vhosts.conf
```

En `C:\xampp\apache\conf\extra\httpd-vhosts.conf` agregue:

```apache
<VirtualHost *:80>
    ServerName personas-minvu.test
    DocumentRoot "C:/proyectos/personas_minvu_nuble/public"
    <Directory "C:/proyectos/personas_minvu_nuble/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Si el proyecto queda en otra carpeta de ese PC, cambie esas dos rutas. Tiene que ser la carpeta `public`.

Abra como administrador `C:\Windows\System32\drivers\etc\hosts` y agregue:

```text
127.0.0.1 personas-minvu.test
```

En el panel de XAMPP pulse **Stop** y luego **Start** en Apache.

## 3. Base de datos

Entre a `http://localhost/phpmyadmin` y cree una base llamada `personas_minvu_nuble`, con cotejamiento `utf8mb4_unicode_ci`.

En el `.env` del proyecto:

```env
APP_URL=http://personas-minvu.test
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personas_minvu_nuble
DB_USERNAME=root
DB_PASSWORD=
```

La contraseña de `root` en XAMPP viene vacía. Si le puso una, escríbala en `DB_PASSWORD`.

## 4. Dejar el sistema listo

En la carpeta del proyecto, use el PHP de XAMPP y no el de otro servidor:

```powershell
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan migrate --force
C:\xampp\php\php.exe artisan db:seed --class=ProductionSeeder --force
C:\xampp\php\php.exe artisan config:cache
npm run build
```

`npm run build` se hace una vez, y de nuevo solo cuando cambien pantallas o estilos. Apache no compila eso solo.

`storage` y `bootstrap/cache` deben permitir escritura al usuario con el que corre Apache.

## 5. Entrar

Al iniciar XAMPP el sistema queda en `http://personas-minvu.test`.

| Correo | Contraseña |
| --- | --- |
| `admin@minvu-nuble.cl` | `Minvu.Nuble.2026` |

Cambie esa contraseña en **Mi ficha** al entrar. `ProductionSeeder` no carga funcionarios ni registros de ejemplo. Si la cuenta ya existe, no le cambia la contraseña.

## Si Apache no arranca

Casi siempre el puerto 80 ya lo usa otro programa. En el panel, **Netstat** muestra quién lo ocupa. Hay que detener ese programa o cambiar el puerto de Apache.
