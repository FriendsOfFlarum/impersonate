import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import username from 'flarum/helpers/username';

export default class ImpersonatelModal extends Modal {
    init() {
        super.init();
        this.user = this.props.user;
        this.reason = m.prop('');
    }

    className() {
        return 'ImpersonateModal Modal--medium';
    }

    title() {
        return app.translator.trans('fof-impersonate.forum.modal.title');
    }

    content() {
        return (
            <div className="Modal-body">
                <div className="Form Form--centered">
                    <div className="Form-group">
                        <textarea
                            className="FormControl"
                            value={this.reason()}
                            placeholder={app.translator.trans('fof-impersonate.forum.modal.placeholder')}
                            oninput={m.withAttr('value', this.reason)}
                            rows="4"
                        />
                    </div>
                    <div className="Form-group">
                        {Button.component({
                            className: 'Button Button--primary Button--block',
                            type: 'submit',
                            loading: this.loading,
                            children: app.translator.trans('fof-impersonate.forum.modal.impersonate_username', {
                                username: username(this.user),
                            }),
                        })}
                    </div>
                </div>
            </div>
        );
    }

    onsubmit(e) {
        e.preventDefault();

        app.store
            .createRecord('impersonate')
            .save({
                userId: this.user.id(),
                reason: this.reason(),
            })
            .then(this.props.callback);
    }
}
