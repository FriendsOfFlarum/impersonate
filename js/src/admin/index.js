import {extend} from 'flarum/extend';
import app from 'flarum/app';
import PermissionGrid from 'flarum/components/PermissionGrid';

app.initializers.add('flagrow/impersonate', () => {
    extend(PermissionGrid.prototype, 'moderateItems', items => {
        items.add('flagrow-impersonate-login', {
            icon: 'fas fa-id-card',
            label: app.translator.trans('flagrow-impersonate.admin.permissions.login'),
            permission: 'flagrow-impersonate.login',
        });
    });
});
