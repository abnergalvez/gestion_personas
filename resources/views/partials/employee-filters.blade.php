<x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
<x-form.input name="employee_search" label="Buscar funcionario" :value="request('employee_search')" placeholder="Nombre, RUT, cargo o correo" />
<x-form.select name="employee_department_id" label="Departamento del funcionario" :options="$departments" :selected="request('employee_department_id')" placeholder="Todos" />
<x-form.select name="employee_section_id" label="Sección del funcionario" :options="$sections" :selected="request('employee_section_id')" placeholder="Todas" />
<x-form.select name="employment_quality" label="Calidad del funcionario" :options="$qualities" :selected="request('employment_quality')" placeholder="Todas" />
<x-form.select name="staff_level" label="Estamento" :options="$staffLevels" :selected="request('staff_level')" placeholder="Todos" />
<x-form.select name="sex" label="Sexo" :options="$sexes" :selected="request('sex')" placeholder="Todos" />
