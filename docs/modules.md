# Cómo agregar un módulo

Ejemplo: un mantenedor de "comisiones de servicio". Los mismos pasos sirven para una entidad ligada al funcionario.

1. Cree la migración con nombres en inglés y claves foráneas. Si el registro es del funcionario, use `employee_id` con `cascadeOnDelete` y borre también los hijos desde Eloquent cuando haya archivos.
2. Cree el modelo en `app/Models` con `$fillable`, `casts()` y las relaciones. Si guarda archivos, use `DeletesStoredFiles` y liste las columnas en `storedFiles()`.
3. Si hay una lista cerrada, cree un enum en `app/Enums` con el trait `HasOptions` y un método `label()` en español. Agregue `tone()` solo si el valor se muestra como insignia.
4. Cree un Form Request. Pase los identificadores vacíos a `null` con `NullifiesEmptyFields`. Si el formulario puede volver a la ficha, incluya `ValidatesEmployeeContext`.
5. Cree el controlador de recurso. Deje la validación en el request y la redirección en `RedirectsToEmployeeContext` cuando corresponda. El mensaje flash va en español.
6. Registre la ruta en `routes/web.php`, dentro del grupo `auth`. Si es un mantenedor, déjela dentro del grupo `superadmin`. Agregue el recurso en `App\Support\ActivityLogger` para que el registro de acciones lo describa en español.
7. Agregue el ítem en `App\Support\Navigation::groups()`. Si hace falta un icono nuevo, agréguelo en `resources/views/components/icon.blade.php`.
8. Cree las vistas con el layout `layouts.app`, `x-page-header`, `x-card` y los componentes de formulario. Incluya el filtro por funcionario en el listado y el enlace desde `employees/show`.
9. Si sube archivos, use `StoredUpload`, acepte solo PDF o imagen, y permita verlos con `x-file-link`. Agregue el prefijo de carpeta en `FileController` si es uno nuevo.
10. Sume datos de ejemplo en un seeder y un caso en `tests/Feature` si el módulo valida una regla propia.
11. No cree una paleta, una tipografía ni un layout distintos. Revise celular y escritorio.

Después de las vistas, ejecute `npm run build` para publicar los estilos.
