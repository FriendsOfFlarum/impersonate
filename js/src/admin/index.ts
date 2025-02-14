import app from 'flarum/admin/app';
import extendUserListPage from './extendUserListPage';

export { default as extend } from './extend';

app.initializers.add('fof-impersonate', () => {
  app.registry.for('fof-impersonate').registerPermission(
    {
      icon: 'fas fa-id-card',
      label: app.translator.trans('fof-impersonate.admin.permissions.login'),
      permission: 'fof-impersonate.login',
    },
    'moderate'
  );

  if (app.initializers.has('fof-moderator-notes')) {
    app.extensionData.for('fof-impersonate').registerSetting({
      setting: 'fof-impersonate.require_reason',
      type: 'boolean',
      label: app.translator.trans('fof-impersonate.admin.settings.require_reason'),
    });
  }

  extendUserListPage();
});
