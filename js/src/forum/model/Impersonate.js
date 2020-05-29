import Model from 'flarum/Model';

export default class Impersonate extends Model {
    id = Model.attribute('id');
    reason = Model.attribute('reason');
}
