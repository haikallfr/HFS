<?php

return [
    'permission_groups' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'description' => 'Akses ringkasan operasional dan dashboard eksekutif.',
            'permissions' => [
                'dashboard.view' => 'Lihat dashboard utama',
                'reports.executive.view' => 'Lihat dashboard eksekutif/manajemen',
            ],
        ],
        [
            'key' => 'rbac',
            'label' => 'RBAC & User',
            'description' => 'Kelola user, role, dan permission sistem.',
            'permissions' => [
                'rbac.manage' => 'Kelola role dan permission matrix',
                'users.manage' => 'Kelola data user',
            ],
        ],
        [
            'key' => 'fleet',
            'label' => 'Fleet Management',
            'description' => 'Operasional HM, fuel, dan service alat berat.',
            'permissions' => [
                'fleet.view' => 'Lihat modul fleet',
                'fleet.manage' => 'Kelola master dan operasional fleet',
                'fleet.hm.input' => 'Input HM lapangan',
                'fleet.fuel.input' => 'Input fuel control',
                'fleet.service.manage' => 'Kelola preventive maintenance',
            ],
        ],
        [
            'key' => 'camera',
            'label' => 'Smart Camera',
            'description' => 'Hak akses kamera perangkat dan upload hasil capture.',
            'permissions' => [
                'camera.capture' => 'Gunakan kamera perangkat',
                'camera.upload' => 'Upload foto hasil capture',
            ],
        ],
        [
            'key' => 'procurement',
            'label' => 'Procurement',
            'description' => 'Alur PR, approval, PO, dan penerimaan barang.',
            'permissions' => [
                'procurement.pr.create' => 'Buat PR',
                'procurement.pr.approve' => 'Approve PR',
                'procurement.po.manage' => 'Kelola PO',
                'procurement.do.receive' => 'Terima DO / receiving',
            ],
        ],
        [
            'key' => 'inventory',
            'label' => 'Inventory',
            'description' => 'Kontrol stok dan audit trail inventory.',
            'permissions' => [
                'inventory.view' => 'Lihat inventory',
                'inventory.manage' => 'Kelola inventory',
                'inventory.audit.view' => 'Lihat audit trail inventory',
            ],
        ],
    ],
    'permissions' => [
        'dashboard.view',
        'reports.executive.view',
        'rbac.manage',
        'users.manage',
        'fleet.view',
        'fleet.manage',
        'fleet.hm.input',
        'fleet.fuel.input',
        'fleet.service.manage',
        'camera.capture',
        'camera.upload',
        'procurement.pr.create',
        'procurement.pr.approve',
        'procurement.po.manage',
        'procurement.do.receive',
        'inventory.view',
        'inventory.manage',
        'inventory.audit.view',
    ],
    'navigation' => [
        [
            'label' => 'Executive Dashboard',
            'route' => 'dashboard',
            'permission' => 'dashboard.view',
        ],
        [
            'label' => 'User & Role Management',
            'route' => 'admin.users-roles',
            'permission' => 'rbac.manage',
        ],
        [
            'label' => 'Fleet Management',
            'route' => 'fleet.hm-entry',
            'permission' => 'fleet.view',
        ],
        [
            'label' => 'Procurement',
            'route' => 'dashboard',
            'permission' => 'procurement.pr.create',
        ],
        [
            'label' => 'Inventory',
            'route' => 'dashboard',
            'permission' => 'inventory.view',
        ],
    ],
];
