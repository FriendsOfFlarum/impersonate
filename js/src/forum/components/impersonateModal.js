import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import username from 'flarum/helpers/username';

export default class ImpersonateModal extends Modal {
    init() {
        super.init();
        this.user = this.props.user;
        this.reason = m.prop('');
        this.loading = false;
        this.reasonEnabled = app.forum.attribute('impersonateEnableReason');
        this.reasonRequired = app.forum.attribute('impersonateReasonRequired');
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
                <div>
                    <p>
                        {app.translator.trans('fof-impersonate.forum.modal.label', {
                            username: username(this.user),
                        })}
                    </p>
                </div>
                <div className="Form Form--centered">
                    {this.reasonEnabled ? (
                        <div className="Form-group">
                            <textarea
                                className="FormControl"
                                value={this.reason()}
                                placeholder={
                                    this.reasonRequired
                                        ? app.translator.trans('fof-impersonate.forum.modal.placeholder_required')
                                        : app.translator.trans('fof-impersonate.forum.modal.placeholder_optional')
                                }
                                oninput={m.withAttr('value', this.reason)}
                                rows="4"
                            />
                        </div>
                    ) : (
                        ''
                    )}
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
        this.loading = true;

        app.store.createRecord('impersonate').save({
            userId: this.user.id(),
            reason: this.reason(),
        }),
            { errorHandler: this.onerror.bind(this) }.then(this.props.callback).catch(() => {});
    }
}
