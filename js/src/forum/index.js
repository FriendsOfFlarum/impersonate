import {extend} from 'flarum/extend';
import app from 'flarum/app';
import UserControls from 'flarum/utils/UserControls';
import Button from 'flarum/components/Button';
import Model from 'flarum/Model';
import User from 'flarum/models/User';

app.initializers.add('flagrow/impersonate', () => {
    User.prototype.flagrowCanImpersonate = Model.attribute('flagrowCanImpersonate');

    extend(UserControls, 'moderationControls', (items, user) => {
        if (user.flagrowCanImpersonate()) {
            items.add('flagrow-impersonate-login', Button.component({
                children: app.translator.trans('flagrow-impersonate.forum.user_controls.impersonate_button'),
                icon: 'fas fa-id-card',
                onclick() {
                    app.request({
                        method: 'POST',
                        url: `${app.forum.attribute('apiUrl')}/flagrow/impersonate/${user.id()}`,
                    }).then(() => {
                        window.location.reload();
                    });
                },
            }));
        }
    });
});
