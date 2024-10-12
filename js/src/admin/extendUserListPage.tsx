import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import UserListPage from 'flarum/admin/components/UserListPage';
import LoginAsUserButton from '../common/components/LoginAsUserButton';

import type ItemList from 'flarum/common/utils/ItemList';
import type User from 'flarum/common/models/User';
import type Mithril from 'mithril';

export default function extendUserListPage() {
  extend(UserListPage.prototype, 'userActionItems', function (items: ItemList<Mithril.Children>, user: User) {
    const forumBaseUrl = app.forum.attribute('baseUrl');

    items.add('impersonate', <LoginAsUserButton user={user} redirectTo={`${forumBaseUrl}/u/${user.slug()}`} />);
  });
}
