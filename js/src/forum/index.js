import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserControls from 'flarum/forum/utils/UserControls';
import Button from 'flarum/common/components/Button';
import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';
import ImpersonateModal from './components/impersonateModal';
import ImpersonateModel from './model/Impersonate';

app.initializers.add('fof-impersonate', () => {
  User.prototype.canFoFImpersonate = Model.attribute('canFoFImpersonate');
  User.prototype.impersonateReasonRequired = Model.attribute('impersonateReasonRequired');
  app.store.models.impersonate = ImpersonateModel;

  extend(UserControls, 'moderationControls', (items, user) => {
    if (user.canFoFImpersonate()) {
      items.add(
        'fof-impersonate-login',
        Button.component(
          {
            icon: 'fas fa-id-card',
            onclick() {
              app.modal.show(ImpersonateModal, {
                callback: () => window.location.reload(),
                user,
              });
            },
          },
          app.translator.trans('fof-impersonate.forum.user_controls.impersonate_button')
        )
      );
    }
  });
});
