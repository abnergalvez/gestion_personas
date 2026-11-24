# Instalar en XAMPP para Windows

Apache de XAMPP sirve el sistema al arrancar. No hace falta `php artisan serve`. La carpeta publicada es `public`, no la raíz del proyecto.

Use **XAMPP 8.4.28 para Windows de 64 bits**, desde [apachefriends.org/download](https://www.apachefriends.org/download.html). Trae PHP 8.4.28 y MariaDB 10.11.19. MariaDB se configura como MySQL. El instalador 8.3 se queda corto y el 8.5 no es el que se usó para armar este sistema.

XAMPP no incluye Composer ni Node. En ese PC también hacen falta Composer 2 y Node.js 20.

## 1. Que Apache y MySQL partan con XAMPP

En el Panel de control de XAMPP, abra **Config** (arriba a la derecha) y entre en **Autostart of modules**. Marque **Apache** y **MySQL**. Al abrir XAMPP, ambos quedan en verde.

Si además quiere que partan al encender Windows, en la columna **Service** marque la casilla de Apache y la de MySQL. Windows pide permisos de administrador e instala los dos servicios.

## 2. Decirle a Apache dónde está el sistema

Abra `C:\xampp\apache\conf\httpd.conf` y confirme que estas líneas no tengan `#` al inicio:

```apache
Listen 80
Include conf/extra/httpd-vhosts.conf
```

`Listen 80` acepta conexiones de la red. `Listen 127.0.0.1:80` las deja solo en este PC: si aparece así, déjela en `Listen 80`.

XAMPP ya trae, al inicio de `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, un sitio para `localhost` con esta carpeta:

```apache
DocumentRoot "C:/xampp/htdocs"
```

Ese es el primero y, por eso, `http://127.0.0.1` abre la página de bienvenida de XAMPP. No basta con pegar otro bloque debajo: hay que reemplazar ese `VirtualHost`.

Déjelo así, y comente o borre el bloque anterior que apunta a `htdocs`:

```apache
<VirtualHost *:80>
    ServerName localhost
    ServerAlias 127.0.0.1 personas-minvu.test 192.168.1.40
    DocumentRoot "C:/proyectos/personas_minvu_nuble/public"
    <Directory "C:/proyectos/personas_minvu_nuble/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

`192.168.1.40` es un ejemplo. Cámbiela por la IP de este PC en la red, la del paso de acceso desde otros equipos. Este bloque tiene que quedar antes que cualquier otro `VirtualHost` del puerto 80.

Si el proyecto queda en otra carpeta de ese PC, cambie las dos rutas. Tiene que ser la carpeta `public`.

phpMyAdmin sigue en `http://127.0.0.1/phpmyadmin`. No depende de la carpeta `htdocs`.

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

Al iniciar XAMPP el sistema queda en `http://127.0.0.1` y también en `http://personas-minvu.test`. Si `127.0.0.1` sigue mostrando la bienvenida de XAMPP, el bloque de `htdocs` sigue siendo el primero: coméntelo y reinicie Apache.

| Correo | Contraseña |
| --- | --- |
| `admin@minvu-nuble.cl` | `Minvu.Nuble.2026` |

Cambie esa contraseña en **Mi ficha** al entrar. `ProductionSeeder` no carga funcionarios ni registros de ejemplo. Si la cuenta ya existe, no le cambia la contraseña.

Cuando los departamentos y las secciones ya existan con el mismo nombre de la planilla, cargue la dotación. Se puede ejecutar de nuevo: actualiza cada funcionario por su RUT.

```powershell
C:\xampp\php\php.exe artisan db:seed --class=EmployeeRosterSeeder --force
```

## 6. Acceder desde otros equipos de la red

En el PC donde está XAMPP, abra PowerShell y ejecute:

```powershell
ipconfig
```

Anote la **Dirección IPv4** del adaptador que está conectado a la red, por ejemplo `192.168.1.40`. No use `127.0.0.1`: esa dirección solo existe dentro del mismo equipo.

Ponga esa IP en el `ServerAlias` del paso 2, junto a `127.0.0.1`, y reinicie Apache.

En el `.env`, deje `APP_URL` con la misma dirección. Los enlaces, el logo y los archivos se arman con ese valor. Si queda en `personas-minvu.test`, los otros equipos intentarán abrir un nombre que no conocen.

```env
APP_URL=http://192.168.1.40
SESSION_DOMAIN=
```

`SESSION_DOMAIN` debe quedar vacío. Si apunta al nombre `.test`, el ingreso desde la IP no mantiene la sesión.

Después de guardar el `.env`:

```powershell
C:\xampp\php\php.exe artisan config:cache
```

Abra el Firewall de Windows Defender, entre en **Configuración avanzada**, **Reglas de entrada** y cree una regla nueva:

1. Tipo: **Puerto**.
2. TCP, puerto **80**.
3. **Permitir la conexión**.
4. Márquela solo para el perfil **Privado**.

También puede, la primera vez que Apache arranca, aceptar el aviso de Windows y permitir Apache en redes privadas.

Desde otro computador o un celular en la misma red Wi-Fi o cable, abra:

```text
http://192.168.1.40
```

Use la IP real del equipo. No hace falta editar el archivo `hosts` de los demás aparatos. El usuario y la contraseña son los mismos del paso 5.

MySQL se queda en este PC (`127.0.0.1`). Los otros equipos entran solo al sitio; no abra el puerto 3306 en el firewall.

Si el router entrega otra IP al reiniciar, el acceso deja de coincidir. Vuelva a mirar `ipconfig`, actualice `ServerAlias` y `APP_URL`, y ejecute de nuevo `config:cache`. En el router puede reservar esa IP para este equipo y así no cambia.

Este acceso es para la red interna. No reenvíe el puerto 80 hacia internet.

## Si Apache no arranca

Casi siempre el puerto 80 ya lo usa otro programa. En el panel, **Netstat** muestra quién lo ocupa. Hay que detener ese programa o cambiar el puerto de Apache.
