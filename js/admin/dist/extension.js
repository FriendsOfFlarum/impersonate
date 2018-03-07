'use strict';

System.register('flagrow/impersonate/main', ['flarum/extend', 'flarum/app', 'flarum/components/PermissionGrid'], function (_export, _context) {
    "use strict";

    var extend, app, PermissionGrid;
    return {
        setters: [function (_flarumExtend) {
            extend = _flarumExtend.extend;
        }, function (_flarumApp) {
            app = _flarumApp.default;
        }, function (_flarumComponentsPermissionGrid) {
            PermissionGrid = _flarumComponentsPermissionGrid.default;
        }],
        execute: function () {

            app.initializers.add('flagrow-impersonate', function () {
                extend(PermissionGrid.prototype, 'moderateItems', function (items) {
                    items.add('flagrow-impersonate-login', {
                        icon: 'id-card',
                        label: app.translator.trans('flagrow-impersonate.admin.permissions.login'),
                        permission: 'flagrow-impersonate.login'
                    });
                });
            });
        }
    };
});