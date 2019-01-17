<?php

namespace Exceedone\Exment\Model;
use Exceedone\Exment\Enums\RoleValue;

/**
 * Define short summary.
 *
 * Define description.
 *
 * @version 1.0
 * @author h-sato
 */
class Define
{
    public const COMPOSER_PACKAGE_NAME = 'exceedone/exment';
    public const COMPOSER_VERSION_CHECK_URL = 'https://repo.packagist.org/p/exceedone/exment.json';

    public const RULES_REGEX_VALUE_FORMAT = '\${(.*?)\}';
    public const RULES_REGEX_ALPHANUMERIC_UNDER_HYPHEN = '^[a-zA-Z0-9\-_]*$';
    public const RULES_REGEX_SYSTEM_NAME = '^(?=[a-zA-Z]{1,32})[a-zA-Z][-_a-zA-Z0-9]+$';
    
    public const SYSTEM_SETTING_NAME_VALUE = [
        'initialized' => ['type' => 'boolean', 'default' => '0', 'group' => 'initialize'],
        'site_name' => ['default' => 'Exment', 'group' => 'initialize'],
        'site_name_short' => ['default' => 'Exm', 'group' => 'initialize'],
        'site_logo' => ['type' => 'file', 'move' => 'system', 'group' => 'initialize'],
        'site_logo_mini' => ['type' => 'file', 'move' => 'system', 'group' => 'initialize'],
        'site_skin' => ['config' => 'admin.skin', 'group' => 'initialize'],
        'permission_available' => ['type' => 'boolean', 'default' => '1', 'group' => 'initialize'],
        'organization_available' => ['type' => 'boolean', 'default' => '1', 'group' => 'initialize'],
        ///'system_role' => ['type' => 'json'],
        'system_mail_from' => ['default' => 'no-reply@hogehoge.com', 'group' => 'initialize'],
        'site_layout' => ['default' => 'layout_default', 'group' => 'initialize'],
        // cannot call getValue function
        'backup_enable_automatic' => ['type' => 'boolean', 'default' => '0', 'group' => 'backup'],
        'backup_automatic_term' => ['type' => 'int', 'default' => '1', 'group' => 'backup'],
        'backup_automatic_hour' => ['type' => 'int', 'default' => '3', 'group' => 'backup'],
        'backup_target' => ['type' => 'array', 'default' => 'database,plugin,attachment,log,config', 'group' => 'backup'] ,
        'backup_automatic_executed' => ['type' => 'datetime'],
    ];

    public const SYSTEM_SKIN = [
        "skin-blue",
        "skin-blue-light",
        "skin-yellow",
        "skin-yellow-light",
        "skin-green",
        "skin-green-light",
        "skin-purple",
        "skin-purple-light",
        "skin-red",
        "skin-red-light",
        "skin-black",
        "skin-black-light",
    ];

    public const SYSTEM_LAYOUT = [
        'layout_default' => ['sidebar-mini'],
        'layout_mini' => ['sidebar-collapse', 'sidebar-mini'],
    ];

    public const SYSTEM_KEY_SESSION_SYSTEM_CONFIG = "setting.%s";
    public const SYSTEM_KEY_SESSION_INITIALIZE = "initialize";
    public const SYSTEM_KEY_SESSION_AUTHORITY = "role";
    public const SYSTEM_KEY_SESSION_USER_SETTING = "user_setting";
    public const SYSTEM_KEY_SESSION_SYSTEM_VERSION = "system_version";
    public const SYSTEM_KEY_SESSION_ORGANIZATION_IDS = "organization_ids";
    public const SYSTEM_KEY_SESSION_FILE_UPLOADED_UUID = "file_uploaded_uuid";
    public const SYSTEM_KEY_SESSION_TABLE_ACCRSSIBLE_ORGS = "table_accessible_orgs_%s";
    public const SYSTEM_KEY_SESSION_TABLE_ACCRSSIBLE_USERS = "table_accessible_users_%s";
    public const SYSTEM_KEY_SESSION_VALUE_ACCRSSIBLE_USERS = "value_accessible_users_%s_%s";
    public const SYSTEM_KEY_SESSION_CUSTOM_TABLE_ELOQUENT = "custom_table_get_eloquent_%s";
    public const SYSTEM_KEY_SESSION_CUSTOM_COLUMN_ELOQUENT = "custom_column_get_eloquent_%s";
    public const SYSTEM_KEY_SESSION_ALL_DATABASE_TABLE_NAMES = "all_database_table_names";
    public const SYSTEM_KEY_SESSION_ALL_RECORDS = "all_records_%s";
    public const SYSTEM_KEY_SESSION_ALL_CUSTOM_TABLES = "all_custom_tables";
    public const SYSTEM_KEY_SESSION_DATABASE_COLUMN_NAMES_IN_TABLE = "database_column_names_in_table_%s";

    // Dashboard --------------------------------------------------
    
    public const DASHBOARD_BOX_SYSTEM_PAGES = [
        ['id' => 1, 'name' => 'guideline']
    ];

    public const PLUGIN_EVENT_TRIGGER = [
        'saving',
        'saved',
        'loading',
        'loaded',
        'grid_menubutton',
        'form_menubutton_create',
        'form_menubutton_edit',
    ];

    /**
     * MENU SYSTEM DIFINITION
     */
    public const MENU_SYSTEM_DEFINITION = [
        'home' => [
            'uri' => '/',
            'icon' => 'fa-home',
        ],
        'system' => [
            'uri' => 'system',
            'icon' => 'fa-cogs',
        ],
        'custom_table' => [
            'uri' => 'table',
            'icon' => 'fa-table',
        ],
        'role' => [
            'uri' => 'role',
            'icon' => 'fa-user-secret',
        ],
        'user' => [
            'uri' => 'data/user',
            'icon' => 'fa-users',
        ],
        'organization' => [
            'uri' => 'data/organization',
            'icon' => 'fa-building',
        ],
        'menu' => [
            'uri' => 'auth/menu',
            'icon' => 'fa-sitemap',
        ],
        'template' => [
            'uri' => 'template',
            'icon' => 'fa-clone',
        ],
        'backup' => [
            'uri' => 'backup',
            'icon' => 'fa-database',
        ],
        'plugin' => [
            'uri' => 'plugin',
            'icon' => 'fa-plug',
        ],
        'notify' => [
            'uri' => 'notify',
            'icon' => 'fa-bell',
        ],
        'loginuser' => [
            'uri' => 'loginuser',
            'icon' => 'fa-user-plus',
        ],
        'mail' => [
            'uri' => 'mail',
            'icon' => 'fa-envelope',
        ],
    ];

    public const CUSTOM_COLUMN_AVAILABLE_CHARACTERS_OPTIONS = [
        'lower','upper','number','hyphen_underscore','symbol'
    ];

    public const CUSTOM_COLUMN_CURRENCYLIST = [
        '&yen;' => ['type' => 'before'],
        '円' => ['type' => 'after'],
        '$' => ['type' => 'before'],
    ];

    public const CUSTOM_VALUE_IMPORT_KEY = [
        'id',
        'suuid',
    ];
    public const CUSTOM_VALUE_IMPORT_ERROR = [
        'stop',
        //'skip', //TODO:how to develop
    ];

    public const GRID_CHANGE_PAGE_MENULIST = [
        ['url' => 'table', 'icon' => 'fa-table', 'move_edit' => true, 'roles' => [RoleValue::CUSTOM_TABLE], 'exmtrans' => 'change_page_menu.custom_table'],
        ['url' => 'column', 'icon' => 'fa-list', 'roles' => [RoleValue::CUSTOM_TABLE], 'exmtrans' => 'change_page_menu.custom_column'],
        ['url' => 'relation', 'icon' => 'fa-compress', 'roles' => [RoleValue::CUSTOM_TABLE], 'exmtrans' => 'change_page_menu.custom_relation'],
        ['url' => 'form', 'icon' => 'fa-keyboard-o', 'roles' => [RoleValue::CUSTOM_FORM], 'exmtrans' => 'change_page_menu.custom_form'],
        ['url' => 'view', 'icon' => 'fa-th-list', 'roles' => [RoleValue::CUSTOM_VIEW], 'exmtrans' => 'change_page_menu.custom_view'],
        ['url' => 'copy', 'icon' => 'fa-copy', 'roles' => [RoleValue::CUSTOM_TABLE], 'exmtrans' => 'change_page_menu.custom_copy'],
        ['url' => 'data', 'icon' => 'fa-database', 'roles' => RoleValue::AVAILABLE_VIEW_CUSTOM_VALUE, 'exmtrans' => 'change_page_menu.custom_value'],
    ];

    // Template --------------------------------------------------
    public const TEMPLATE_IMPORT_EXCEL_SHEETNAME = [
        'custom_tables',
        'custom_columns',
        'custom_relations',
        'custom_forms',
        'custom_form_blocks',
        'custom_form_columns',
        'custom_views',
        'custom_view_columns',
        'custom_view_filters',
        'custom_view_sorts',
        'custom_copies',
        'custom_copy_columns',
        'admin_menu',
    ];

    public const CUSTOM_COLUMN_TYPE_PARENT_ID = 0;

    public const FILE_OPTION = [
        'showPreview' => false,
        'showCancel' => false,
    ];
}
