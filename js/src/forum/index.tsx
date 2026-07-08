import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserControls from 'flarum/forum/utils/UserControls';
import Notices from 'flarum/forum/components/Notices';
import SessionDropdown from 'flarum/forum/components/SessionDropdown';
import Alert from 'flarum/common/components/Alert';
import username from 'flarum/common/helpers/username';
import LoginAsUserButton from '../common/components/LoginAsUserButton';
import ReturnToUserButton from './components/ReturnToUserButton';

import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

export { default as extend } from './extend';

app.initializers.add('fof-impersonate', () => {
  extend(UserControls, 'moderationControls', (items, user) => {
    if (user.canFoFImpersonate()) {
      items.add('fof-impersonate-login', <LoginAsUserButton user={user} />);
    }
  });

  extend(Notices.prototype, 'items', function (items: ItemList<Mithril.Children>) {
    if (app.forum.attribute('fofImpersonateOriginalUsername') && app.session.user) {
      items.add(
        'fof-impersonate',
        <Alert
          dismissible={false}
          className="Alert--fofImpersonate"
          containerClassName="container"
          controls={[<ReturnToUserButton className="Button Button--link" />]}
        >
          {app.translator.trans('fof-impersonate.forum.notice.message', { username: <strong>{username(app.session.user)}</strong> })}
        </Alert>,
        110
      );
    }
  });

  extend(SessionDropdown.prototype, 'items', function (items: ItemList<Mithril.Children>) {
    if (app.forum.attribute('fofImpersonateOriginalUsername')) {
      items.add('fof-impersonate-return', <ReturnToUserButton />, -95);
    }
  });
});
