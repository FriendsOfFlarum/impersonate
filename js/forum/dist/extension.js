'use strict';

System.register('flagrow/impersonate/main', ['flarum/extend', 'flarum/app', 'flarum/utils/UserControls', 'flarum/components/Button', 'flarum/Model', 'flarum/models/User'], function (_export, _context) {
    "use strict";

    var extend, app, UserControls, Button, Model, User;
    return {
        setters: [function (_flarumExtend) {
            extend = _flarumExtend.extend;
        }, function (_flarumApp) {
            app = _flarumApp.default;
        }, function (_flarumUtilsUserControls) {
            UserControls = _flarumUtilsUserControls.default;
        }, function (_flarumComponentsButton) {
            Button = _flarumComponentsButton.default;
        }, function (_flarumModel) {
            Model = _flarumModel.default;
        }, function (_flarumModelsUser) {
            User = _flarumModelsUser.default;
        }],
        execute: function () {

            app.initializers.add('flagrow-impersonate', function () {
                User.prototype.flagrowCanImpersonate = Model.attribute('flagrowCanImpersonate');

                extend(UserControls, 'moderationControls', function (items, user) {
                    if (user.flagrowCanImpersonate()) {
                        items.add('flagrow-impersonate-login', Button.component({
                            children: app.translator.trans('flagrow-impersonate.forum.user_controls.impersonate_button'),
                            icon: 'id-card',
                            onclick: function onclick() {
                                app.request({
                                    method: 'POST',
                                    url: app.forum.attribute('apiUrl') + '/flagrow/impersonate/' + user.id()
                                }).then(function () {
                                    window.location.reload();
                                });
                            }
                        }));
                    }
                });
            });
        }
    };
});