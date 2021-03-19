import Model from 'flarum/common/Model';

export default class Impersonate extends Model {
    id = Model.attribute('id');
    reason = Model.attribute('reason');
}
