import { extend } from 'flarum/extend';
import app from 'flarum/app';
import UserControls from 'flarum/utils/UserControls';
import Button from 'flarum/components/Button';
import Model from 'flarum/Model';
import User from 'flarum/models/User';
import ImpersonateModal from './components/impersonateModal';
import ImpersonateModel from './model/Impersonate';

app.initializers.add('fof/impersonate', () => {
    User.prototype.fofCanImpersonate = Model.attribute('fofCanImpersonate');
    app.store.models.impersonate = ImpersonateModel;

    extend(UserControls, 'moderationControls', (items, user) => {
        if (user.fofCanImpersonate()) {
            items.add(
                'fof-impersonate-login',
                Button.component({
                    icon: 'fas fa-id-card',
                    onclick() {
                        app.modal.show(
                            ImpersonateModal, {
                                callback: () => window.location.reload(),
                                user,
                            }
                        );
                    },
                }, app.translator.trans('fof-impersonate.forum.user_controls.impersonate_button'))
            );
        }
    });
});
