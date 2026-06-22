<?php

namespace App\Support\Access;

/**
 * Canonical permission names for the PMS back office.
 */
final class PermissionCatalog
{
    public const SUPER_ADMIN_ROLE = 'super-admin';

    public const PROCUREMENT_OFFICER_ROLE = 'procurement-officer';

    public const PR_REQUESTER_ROLE = 'pr-requester';

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

            'purchase-orders.view' => ['label' => 'View all purchase orders', 'group' => 'Purchase orders'],
            'purchase-orders.view-own' => ['label' => 'View own purchase orders only', 'group' => 'Purchase orders'],
            'purchase-orders.create' => ['label' => 'Create purchase orders', 'group' => 'Purchase orders'],
            'purchase-orders.update' => ['label' => 'Update purchase orders', 'group' => 'Purchase orders'],

            'invoices.create' => ['label' => 'Add invoice', 'group' => 'Invoices'],

            'rfqs.view' => ['label' => 'View RFQs', 'group' => 'RFQs'],
            'rfqs.create' => ['label' => 'Create RFQs', 'group' => 'RFQs'],
            'rfqs.update' => ['label' => 'Update RFQs', 'group' => 'RFQs'],

            'vendor-quotations.view' => ['label' => 'View vendor quotations', 'group' => 'RFQs'],
            'vendor-quotations.create' => ['label' => 'Create vendor quotations', 'group' => 'RFQs'],
            'vendor-quotations.update' => ['label' => 'Update vendor quotations', 'group' => 'RFQs'],

            'quotation-comparison.view' => ['label' => 'View all quotation comparisons', 'group' => 'RFQs'],
            'quotation-comparison.view-own' => ['label' => 'View quotation comparisons for own PRs', 'group' => 'RFQs'],
            'quotation-comparison.select' => ['label' => 'Select preferred vendor quotation', 'group' => 'RFQs'],

            'rfq-terms.view' => ['label' => 'View RFQ general terms', 'group' => 'RFQs'],
            'rfq-terms.manage' => ['label' => 'Manage RFQ general terms', 'group' => 'RFQs'],

            'procurement-requests.view' => ['label' => 'View all procurement requests', 'group' => 'Procurement requests'],
            'procurement-requests.view-own' => ['label' => 'View own procurement requests only', 'group' => 'Procurement requests'],
            'procurement-requests.create' => ['label' => 'Create procurement requests', 'group' => 'Procurement requests'],
            'procurement-requests.update' => ['label' => 'Update procurement requests', 'group' => 'Procurement requests'],

            'projects.view' => ['label' => 'View projects', 'group' => 'Projects'],
            'projects.create' => ['label' => 'Create projects', 'group' => 'Projects'],
            'projects.update' => ['label' => 'Update projects', 'group' => 'Projects'],

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
     * Permissions for employees who create PRs and choose vendor quotations.
     *
     * @return list<string>
     */
    public static function prRequesterPermissions(): array
    {
        return [
            'procurement-requests.view-own',
            'procurement-requests.create',
            'quotation-comparison.view-own',
            'quotation-comparison.select',
            'vendor-quotations.view',
        ];
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

    /**
     * Map a stored permission name to its canonical catalog name.
     */
    public static function canonicalName(string $name): ?string
    {
        $definitions = self::definitions();

        if (array_key_exists($name, $definitions)) {
            return $name;
        }

        if (! preg_match('/^(.+)\.(view|update|manage|delete)-(all|own)$/', $name, $matches)) {
            return null;
        }

        [, $prefix, $action, $scope] = $matches;

        if ($scope === 'own') {
            $ownName = "{$prefix}.{$action}-own";

            if (array_key_exists($ownName, $definitions)) {
                return $ownName;
            }
        }

        if ($action === 'delete') {
            $delete = $prefix.'.delete';
            if (array_key_exists($delete, $definitions)) {
                return $delete;
            }

            $update = $prefix.'.update';
            if (array_key_exists($update, $definitions)) {
                return $update;
            }

            return null;
        }

        $canonical = $prefix.'.'.$action;

        return array_key_exists($canonical, $definitions) ? $canonical : null;
    }
}
