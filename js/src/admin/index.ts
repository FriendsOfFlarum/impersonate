import app from 'flarum/admin/app';
import extendUserListPage from './extendUserListPage';

export { default as extend } from './extend';

app.initializers.add('fof-impersonate', () => {
  extendUserListPage();
});
