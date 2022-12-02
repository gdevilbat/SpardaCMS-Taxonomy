const TermsMaster = () => import('../components/Terms/Master.vue')
const TermsForm = () => import('../components/Terms/Form.vue')

export default class routes{
    constructor(Meta) {
        this.meta = Meta;
    }

    route(){
        return [
            {
                path: 'terms/master',
                name: 'terms-master',
                components : {
                    content : TermsMaster,
                },
                props: { content: true },
                meta: {...this.meta, title_dashboard: 'Terms'}
            },
            {
                path: 'terms/form',
                name: 'terms-form',
                components : {
                    content : TermsForm,
                },
                meta: {...this.meta, title_dashboard: 'Terms'}
            },
        ]
    }
}