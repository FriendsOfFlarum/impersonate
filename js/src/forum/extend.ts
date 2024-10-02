import Extend from 'flarum/common/extenders';
import User from 'flarum/common/models/User';
import Impersonate from './model/Impersonate';

export default [
  new Extend.Store() //
    .add('impersonate', Impersonate),

  new Extend.Model(User) //
    .attribute<boolean>('canFoFImpersonate')
    .attribute<boolean>('impersonateReasonRequired'),
];
