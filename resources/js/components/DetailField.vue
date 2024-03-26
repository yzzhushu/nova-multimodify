<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <div class="flex flex-wrap gap-2">
                <HxTable
                    :columns="columns"
                    :lists="lists"
                    :scrollHeight="initScrollHeight"
                    class="min-w-[24rem] max-w-2xl"
                    style="max-height: calc(360px + .5rem)"
                />
            </div>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

    data() {
        return {
            lists: [],
            initScrollHeight: '360px'
        }
    },

    mounted() {
        Nova.request().post(this.field.options, this.field.value, {
            headers: {'Content-Type': 'application/json'}
        }).then(response => {
            const list = response.data.resources;
            if (typeof list !== 'object' || list.length === 0) return;
            this.initScrollHeight = Math.min((list.length + 1) * 40, 360) + 'px';
            this.lists = this.field.withIndex ?
                list.map(function (item, i) {item.hxTableIndex = i + 1;return item;}) : list;
        });
    },

    computed: {
        columns() {
            const columns = this.field.columns;
            return this.field.withIndex ? (new Proxy([{
                field: 'hxTableIndex',
                header: this.field.tableIndexName || '序号',
                width: this.field.tableIndexWidth || 10,
            }], {})).concat(columns) : columns;
        }
    }
}
</script>
