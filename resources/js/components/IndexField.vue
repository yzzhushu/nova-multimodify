<template>
    <div :class="`text-${field.textAlign}`">
        <div v-if="fieldValue">
            <Tooltip
                :triggers="['hover']"
                :popperTriggers="['hover']"
                placement="top"
                theme="plain"
                @show="loadLists"
                :auto-hide="true"
                distance="6">
                <template #default>
                    <span class="link-default">{{fieldValue}}</span>
                </template>
                <template #content>
                    <HxTable
                        v-if="columns.length > 0"
                        :columns="columns"
                        :lists="lists"
                        :scrollHeight="initScrollHeight"
                        class="min-w-[24rem] max-w-2xl"
                        style="max-height: calc(360px + .5rem)"
                    />
                </template>
            </Tooltip>
        </div>
        <p v-else>&mdash;</p>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'field'],

    data() {
        return {
            lists: [],
            initScrollHeight: '360px'
        }
    },

    methods: {
        loadLists() {
            if (this.lists.length > 0) return;

            Nova.request().post(this.field.options, this.field.value, {
                headers: {'Content-Type': 'application/json'}
            }).then(response => {
                const list = response.data.resources;
                if (typeof list !== 'object' || list.length === 0) return;
                this.initScrollHeight = Math.min((list.length + 1) * 40, 360) + 'px';
                this.lists = this.field.withIndex ?
                    list.map(function (item, i) {item.hxTableIndex = i + 1;return item;}) : list;
            });
        }
    },

    computed: {
        fieldValue() {
            return this.field.displayedAs || (this.field.value.fileName || '');
        },

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
