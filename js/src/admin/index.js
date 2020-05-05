import {extend} from 'flarum/extend';
import app from 'flarum/app';
import PermissionGrid from 'flarum/components/PermissionGrid';

app.initializers.add('fof/impersonate', () => {
    extend(PermissionGrid.prototype, 'moderateItems', items => {
        items.add('fof-impersonate-login', {
            icon: 'fas fa-id-card',
            label: app.translator.trans('fof-impersonate.admin.permissions.login'),
            permission: 'fof-impersonate.login',
        });
    });
});
