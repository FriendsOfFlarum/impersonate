import { extend } from 'flarum/extend';
import app from 'flarum/app';
import PermissionGrid from 'flarum/components/PermissionGrid';
import { settings } from '@fof-components';

const {
    SettingsModal,
    items: { BooleanItem },
} = settings;

app.initializers.add('fof/impersonate', () => {
    app.extensionSettings['fof-impersonate'] = () =>
        app.modal.show(
            SettingsModal, {
                title: app.translator.trans('fof-impersonate.admin.settings.title'),
                type: 'small',
                items: s=> app.forum.attribute('impersonateEnableReason', false)
                    ? [
                          <BooleanItem setting={s} name="fof-impersonate.require_reason">
                              {app.translator.trans('fof-impersonate.admin.settings.require_reason')}
                          </BooleanItem>,
                      ]
                    : [<p>{app.translator.trans('fof-impersonate.admin.settings.no_settings_available')}</p>],
            }
        );

    extend(PermissionGrid.prototype, 'moderateItems', items => {
        items.add('fof-impersonate-login', {
            icon: 'fas fa-id-card',
            label: app.translator.trans('fof-impersonate.admin.permissions.login'),
            permission: 'fof-impersonate.login',
        });
    });
});
