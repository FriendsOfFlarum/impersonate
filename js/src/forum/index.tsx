import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserControls from 'flarum/forum/utils/UserControls';
import LoginAsUserButton from '../common/components/LoginAsUserButton';

export { default as extend } from './extend';

app.initializers.add('fof-impersonate', () => {
  extend(UserControls, 'moderationControls', (items, user) => {
    if (user.canFoFImpersonate()) {
      items.add('fof-impersonate-login', <LoginAsUserButton user={user} />);
    }
  });
});
