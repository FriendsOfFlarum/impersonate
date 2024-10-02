import app from 'flarum/forum/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';

import Button from 'flarum/common/components/Button';
import username from 'flarum/common/helpers/username';
import Stream from 'flarum/common/utils/Stream';
import withAttr from 'flarum/common/utils/withAttr';
import type User from 'flarum/common/models/User';
import type Mithril from 'mithril';
import type { NestedStringArray } from '@askvortsov/rich-icu-message-formatter';
import RequestError from 'flarum/common/utils/RequestError';

export interface ImpersonateModalAttrs extends IInternalModalAttrs {
  user: User;
  callback?: () => void;
}

export default class ImpersonateModal extends Modal<ImpersonateModalAttrs> {
  user!: Stream<User>;
  reason!: Stream<string>;
  loading!: Stream<boolean>;
  reasonEnabled!: Stream<boolean>;
  reasonRequired!: Stream<boolean>;

  oninit(vnode: Mithril.Vnode<ImpersonateModalAttrs, this>): void {
    super.oninit(vnode);

    this.user = this.attrs.user;
    this.reason = Stream('');
    this.loading = false;
    this.reasonEnabled = app.initializers.has('fof-moderator-notes');
    this.reasonRequired = this.user.impersonateReasonRequired();
  }

  className(): string {
    return 'ImpersonateModal Modal--medium';
  }

  title(): NestedStringArray {
    return app.translator.trans('fof-impersonate.forum.modal.title');
  }

  content(): Mithril.Children {
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

  onsubmit(e: Event): void {
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

  onerror(error: RequestError<string>) {
    if (error.status === 422) {
      // @ts-ignore
      error.alert.props.children = app.translator.trans('fof-impersonate.forum.modal.placeholder_required');
    }
    this.loading = false;
    super.onerror(error);
  }
}
