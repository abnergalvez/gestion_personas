<?php

namespace App\Support;

final class Navigation
{
    /**
     * Punto único para agregar secciones al menú lateral.
     *
     * @return list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string}>}>
     */
    public static function groups(): array
    {
        $groups = self::definitions();

        if (! auth()->user()?->isSuperAdmin()) {
            $groups = array_values(array_filter(
                $groups,
                fn (array $group): bool => empty($group['super_admin']),
            ));
        }

        return $groups;
    }

    /**
     * @return list<array{label: string, super_admin?: bool, items: list<array{label: string, route: string, icon: string, active: string}>}>
     */
    private static function definitions(): array
    {
        return [
            [
                'label' => 'General',
                'items' => [
                    self::item('Inicio', 'dashboard', 'home', 'dashboard'),
                ],
            ],
            [
                'label' => 'Personas',
                'items' => [
                    self::item('Funcionarios', 'employees.index', 'users', 'employees.*'),
                ],
            ],
            [
                'label' => 'Organización',
                'items' => [
                    self::item('Departamentos', 'departments.index', 'building', 'departments.*'),
                    self::item('Secciones', 'sections.index', 'squares', 'sections.*'),
                ],
            ],
            [
                'label' => 'Gestión',
                'items' => [
                    self::item('Cursos y capacitaciones', 'training-courses.index', 'academic', 'training-courses.*'),
                    self::item('Licencias médicas', 'medical-leaves.index', 'heart', 'medical-leaves.*'),
                    self::item('Cargas familiares', 'family-dependents.index', 'family', 'family-dependents.*'),
                    self::item('Contratos', 'contracts.index', 'document', 'contracts.*'),
                    self::item('Permisos', 'permits.index', 'clock', 'permits.*'),
                    self::item('Calificaciones', 'evaluations.index', 'star', 'evaluations.*'),
                ],
            ],
            [
                'label' => 'Mantenedores',
                'super_admin' => true,
                'items' => [
                    self::item('Tipos de licencia', 'leave-types.index', 'tag', 'leave-types.*'),
                    self::item('Tipos de permiso', 'permit-types.index', 'tag', 'permit-types.*'),
                    self::item('Tipos de documento', 'contract-document-types.index', 'folder', 'contract-document-types.*'),
                    self::item('Usuarios', 'users.index', 'shield', 'users.*'),
                    self::item('Registro de acciones', 'activity-logs.index', 'list', 'activity-logs.*'),
                ],
            ],
        ];
    }

    /**
     * @return array{label: string, route: string, icon: string, active: string}
     */
    private static function item(string $label, string $route, string $icon, string $active): array
    {
        return compact('label', 'route', 'icon', 'active');
    }
}
