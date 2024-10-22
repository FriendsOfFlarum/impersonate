import Model from 'flarum/common/Model';

export default class Impersonate extends Model {
  reason() {
    return Model.attribute<string>('reason').call(this);
  }
}
