import app from 'flarum/admin/app';

app.initializers.add('fof-impersonate', () => {
  app.extensionData.for('fof-impersonate').registerPermission(
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
});
