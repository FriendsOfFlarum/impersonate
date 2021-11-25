import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import username from 'flarum/common/helpers/username';
import stream from 'flarum/common/utils/Stream';
import withAttr from 'flarum/common/utils/withAttr';

export default class ImpersonateModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);
    this.user = this.attrs.user;
    this.reason = stream('');
    this.loading = false;
    this.reasonEnabled = app.initializers.has('fof-moderator-notes');
    this.reasonRequired = this.user.impersonateReasonRequired();
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
                oninput={withAttr('value', this.reason)}
                rows="4"
              />
            </div>
          ) : (
            ''
          )}
          <div className="Form-group">
            {Button.component(
              {
                className: 'Button Button--primary Button--block',
                type: 'submit',
                loading: this.loading,
              },
              app.translator.trans('fof-impersonate.forum.modal.impersonate_username', {
                username: username(this.user),
              })
            )}
          </div>
        </div>
      </div>
    );
  }

  onsubmit(e) {
    e.preventDefault();
    this.loading = true;

    app.store
      .createRecord('impersonate')
      .save(
        {
          userId: this.user.id(),
          reason: this.reason(),
        },
        { errorHandler: this.onerror.bind(this) }
      )
      .then(this.attrs.callback)
      .catch(() => {});
  }

  onerror(error) {
    if (error.status === 422) {
      error.alert.props.children = app.translator.trans('fof-impersonate.forum.modal.placeholder_required');
    }
    this.loading = false;
    super.onerror(error);
  }
}
