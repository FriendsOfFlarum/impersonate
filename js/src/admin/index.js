import { extend } from 'flarum/extend';
import app from 'flarum/app';
import PermissionGrid from 'flarum/components/PermissionGrid';
import { settings } from '@fof-components';

const {
    SettingsModal,
    items: { BooleanItem, NumberItem, StringItem },
} = settings;

app.initializers.add('fof/impersonate', () => {
    app.extensionSettings['fof-impersonate'] = () =>
        app.modal.show(
            new SettingsModal({
                title: app.translator.trans('fof-impersonate.admin.settings.title'),
                type: 'small',
                items: [
                    <BooleanItem key="fof-impersonate.enable_reason">
                        {app.translator.trans('fof-impersonate.admin.settings.enable_reason')}
                    </BooleanItem>,
                    <BooleanItem key="fof-impersonate.require_reason">
                        {app.translator.trans('fof-impersonate.admin.settings.require_reason')}
                    </BooleanItem>,
                ],
            })
        );

    extend(PermissionGrid.prototype, 'moderateItems', items => {
        items.add('fof-impersonate-login', {
            icon: 'fas fa-id-card',
            label: app.translator.trans('fof-impersonate.admin.permissions.login'),
            permission: 'fof-impersonate.login',
        });
    });
});
