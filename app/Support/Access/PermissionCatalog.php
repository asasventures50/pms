<?php

namespace App\Support\Access;

/**
 * Canonical permission names for the PMS back office.
 */
final class PermissionCatalog
{
    public const SUPER_ADMIN_ROLE = 'super-admin';

    public const PROCUREMENT_OFFICER_ROLE = 'procurement-officer';

    /**
     * @return array<string, array{label: string, group: string}>
     */
    public static function definitions(): array
    {
        return [
            'vendors.view' => ['label' => 'View vendors', 'group' => 'Vendors'],
            'vendors.create' => ['label' => 'Create vendors', 'group' => 'Vendors'],
            'vendors.update' => ['label' => 'Update vendors', 'group' => 'Vendors'],

            'categories.view' => ['label' => 'View categories', 'group' => 'Categories'],
            'categories.create' => ['label' => 'Create categories', 'group' => 'Categories'],
            'categories.update' => ['label' => 'Update categories', 'group' => 'Categories'],
            'categories.import' => ['label' => 'Import categories', 'group' => 'Categories'],
            'categories.export' => ['label' => 'Export categories', 'group' => 'Categories'],

            'purchase-orders.view' => ['label' => 'View purchase orders', 'group' => 'Purchase orders'],
            'purchase-orders.create' => ['label' => 'Create purchase orders', 'group' => 'Purchase orders'],
            'purchase-orders.update' => ['label' => 'Update purchase orders', 'group' => 'Purchase orders'],

            'rfqs.view' => ['label' => 'View RFQs', 'group' => 'RFQs'],
            'rfqs.create' => ['label' => 'Create RFQs', 'group' => 'RFQs'],
            'rfqs.update' => ['label' => 'Update RFQs', 'group' => 'RFQs'],

            'procurement-requests.view' => ['label' => 'View procurement requests', 'group' => 'Procurement requests'],
            'procurement-requests.create' => ['label' => 'Create procurement requests', 'group' => 'Procurement requests'],
            'procurement-requests.update' => ['label' => 'Update procurement requests', 'group' => 'Procurement requests'],

            'locations.view' => ['label' => 'View locations', 'group' => 'Locations'],
            'locations.manage' => ['label' => 'Manage countries and cities', 'group' => 'Locations'],

            'users.view' => ['label' => 'View users', 'group' => 'Access control'],
            'users.create' => ['label' => 'Create users', 'group' => 'Access control'],
            'users.update' => ['label' => 'Update users', 'group' => 'Access control'],
            'users.delete' => ['label' => 'Delete users', 'group' => 'Access control'],

            'roles.view' => ['label' => 'View roles', 'group' => 'Access control'],
            'roles.create' => ['label' => 'Create roles', 'group' => 'Access control'],
            'roles.update' => ['label' => 'Update roles', 'group' => 'Access control'],
            'roles.delete' => ['label' => 'Delete roles', 'group' => 'Access control'],

            'activity-logs.view' => ['label' => 'View activity log', 'group' => 'Access control'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * Permissions granted to procurement officers (everything except access control).
     *
     * @return list<string>
     */
    public static function procurementOfficerPermissions(): array
    {
        return array_values(array_filter(
            self::names(),
            static fn (string $name) => ! str_starts_with($name, 'users.')
                && ! str_starts_with($name, 'roles.')
        ));
    }

    /**
     * @return array<string, list<string>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::definitions() as $name => $meta) {
            $groups[$meta['group']][] = $name;
        }

        return $groups;
    }
}
