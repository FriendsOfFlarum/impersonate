import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserControls from 'flarum/forum/utils/UserControls';
import Button from 'flarum/common/components/Button';
import ImpersonateModal from './components/impersonateModal';

export { default as extend } from './extend';

app.initializers.add('fof-impersonate', () => {
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
