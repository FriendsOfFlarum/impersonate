import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';
import { default as commonExtend } from '../common/extend';

export default [
  ...commonExtend,

  new Extend.Admin() //
    .permission(
      () => ({
        icon: 'fas fa-id-card',
        label: app.translator.trans('fof-impersonate.admin.permissions.login'),
        permission: 'fof-impersonate.login',
      }),
      'moderate'
    )
    .setting(() => ({
      setting: 'fof-impersonate.require_reason',
      type: 'boolean',
      label: app.translator.trans('fof-impersonate.admin.settings.require_reason'),
    })),
];
