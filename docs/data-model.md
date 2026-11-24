# Modelo de datos

Los nombres de tablas y columnas están en inglés. La interfaz los muestra en español.

## Personas y organización

`employees` guarda la ficha vigente: RUT único, nombres, apellidos, nacimiento, sexo opcional (`male` hombre o `female` mujer), profesión, estamento (`staff_level`), estado civil, grado, fecha de ingreso (`hired_on`), correo, calidad (`contract` contrata, `fee` honorario o `permanent` planta), departamento, sección y ruta de la foto. Sexo, estamento, estado civil, ingreso, correo, departamento y sección pueden ser nulos. La edad no se guarda: se calcula con la fecha de nacimiento.

Esa asignación (`department_id`, `section_id`) indica dónde está destinado el funcionario. Es independiente de los roles de jefatura.

`departments` y `sections` tienen `head_employee_id` y `deputy_employee_id`. Una sección puede pertenecer a un departamento (`department_id` nulo si todavía no se define). Los analistas van en las tablas puente `department_analysts` y `section_analysts`. El jefe y el subrogante no se repiten en esa lista: cada unidad tiene una jefatura, una subrogancia y cero o más analistas. Una persona puede cumplir roles en unidades distintas.

## Registros del funcionario

| Tabla | Relación | Contenido |
| --- | --- | --- |
| `training_courses` | pertenece a `employees` | nombre, duración, certificación, vigencia y documento |
| `medical_leaves` | funcionario y `leave_types` | inicio, término, días y tres archivos |
| `family_dependents` | funcionario | nombre, RUT, nacimiento, escolaridad, parentesco y dos archivos. El RUT es único dentro del mismo funcionario |
| `contracts` | funcionario, departamento y sección opcionales | calidad, modalidad, grado, cargo y fechas. El término puede ser nulo |
| `contract_documents` | contrato y `contract_document_types` | un archivo obligatorio |
| `permits` | funcionario y `permit_types` | inicio y término con hora, y un documento. Son permisos de personal, no de acceso al sistema |
| `evaluations` | funcionario | años del periodo, proceso, nota de 1 a 7, descripción y documento |

## Catálogos

- `leave_types`: nombre, descripción y `internal_code` único.
- `permit_types`: nombre único y descripción.
- `contract_document_types`: nombre único y descripción.

No se puede eliminar un catálogo que ya está usado.

## Calidad contractual

En la ficha, `employment_quality` describe la situación vigente y solo admite contrata u honorario, como se pidió para el funcionario.

En `contracts.quality` queda el historial y admite además planta (`permanent`) y suplencia (`substitution`). La modalidad es contrato directo (`direct`) o concurso (`contest`).

## Procesos de calificación

`initial`, `in_evaluation`, `pre_qualification`, `appeal` y `final`.

## Archivos

Cada columna `*_path` guarda una ruta relativa del disco privado, por ejemplo `employees/photos/abc.jpg`. Al reemplazar o borrar el registro, el archivo se elimina del disco.

## Cuentas y registro

`users.role` distingue `super_admin` y `admin`. La cuenta inicial del seeder es super administrador. El avatar sigue en `avatar_path`.

`activity_logs` guarda quién hizo la acción (`user_id`, y una copia de nombre y correo por si la cuenta se elimina), la acción, una descripción en español, el registro afectado, la URL, el método, la IP, el navegador y un JSON con los datos enviados. Ese JSON no incluye contraseñas. El registro no se edita ni se borra desde la interfaz. Al eliminar un usuario, sus filas permanecen y `user_id` queda nulo.
