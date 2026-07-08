import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import type { IButtonAttrs } from 'flarum/common/components/Button';

export default class ReturnToUserButton extends Button<IButtonAttrs> {
  loading: boolean = false;

  view() {
    return (
      <Button {...this.attrs} icon="fas fa-undo" loading={this.loading} onclick={this.returnToOriginalUser.bind(this)}>
        {app.translator.trans('fof-impersonate.forum.return.button', {
          username: app.forum.attribute<string>('fofImpersonateOriginalUsername'),
        })}
      </Button>
    );
  }

  returnToOriginalUser(): void {
    this.loading = true;
    m.redraw();

    app
      .request({
        method: 'POST',
        url: `${app.forum.attribute('apiUrl')}/impersonate/return`,
      })
      .then(() => window.location.reload())
      .catch(() => {
        this.loading = false;
        m.redraw();
      });
  }
}
