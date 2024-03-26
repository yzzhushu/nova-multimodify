<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div class="w-full flex justify-between">
                <div>
                    <label class="h-9">
                        <span class="border cursor-pointer rounded text-sm font-bold h-9
                            focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600
                            inline-flex items-center justify-center shadow px-3 bg-primary-500
                            border-primary-500 hover:[&:not(:disabled)]:bg-primary-400
                            hover:[&:not(:disabled)]:border-primary-400 text-white dark:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>&nbsp;{{uploadButton || uploadButtonLabel}}
                        </span>
                        <input type="file" class="hidden" @change="uploadFile"
                               accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                    </label>
                    <span v-if="errorMessage" class="ml-2 text-red-500 font-bold uppercase">{{errorMessage}}</span>
                </div>
                <div v-if="uploadButton">
                    <span class="border cursor-pointer rounded text-sm font-bold h-9
                        focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600
                        inline-flex items-center justify-center shadow px-3 bg-red-500 border-red-500
                        hover:[&:not(:disabled)]:bg-red-400 hover:[&:not(:disabled)]:border-red-400 text-white dark:text-gray-900" @click="removeFile">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>&nbsp;{{removeButtonLabel}}
                    </span>
                </div>
            </div>
            <div class="w-full">
                <HxTable
                    v-if="lists.length > 0"
                    :columns="columns"
                    :lists="lists"
                    scrollHeight="300px"
                    style="height: 300px;padding-left: 0;padding-right: 0;"
                    class="form-control form-input form-control-bordered"
                />
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {DependentFormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [DependentFormField, HandlesValidationErrors],

    data() {
        return {
            uploadButton: '',
            errorMessage: '',
            lists: [],
            check: {}
        }
    },

    computed: {
        uploadButtonLabel() {
            return this.currentField.uploadButton || '点我选择文件';
        },
        removeButtonLabel() {
            return this.currentField.removeButton || '清除所选文件';
        },
        columns() {
            const columns = this.currentField.columns;
            return this.currentField.withIndex ? (new Proxy([{
                field: 'hxTableIndex',
                header: this.currentField.tableIndexName || '序号',
                width: this.currentField.tableIndexWidth || 10,
            }], {})).concat(columns) : columns;
        }
    },

    methods: {
        // 删除已选文件，并清空相关参数值
        removeFile() {
            this.lists = [];
            this.check = {};
            this.uploadButton = '';
            this.errorMessage = '';
        },

        // 上传文件并获取处理后的结果用于展示
        uploadFile(event) {
            if (event.target.files.length === 0)
                return this.removeFile();
            this.lists = [];
            this.check = {};

            const file = event.target.files[0];
            const size = this.currentField.maxSize;
            if (file.size > size) {
                this.uploadButton = file.name;
                this.errorMessage = '文件需小于: ' + (size / 1024).toFixed(3) + 'KB';
                return;
            }
            this.uploadButton = '上传中...';
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('file', file);
            Nova.request().post(this.currentField.options, formData, {
                headers: {'Content-Type': 'multipart/form-data'}
            }).then(response => {
                this.uploadButton = file.name;
                this.handleData(response, file.name);
            });
        },

        // 初始化数据
        setInitialValue() {
            const value = this.currentField.value;
            if (value === null || typeof value !== 'object') return;
            if (value.data === undefined && value.file === undefined) return;

            this.uploadButton = '加载中...';
            Nova.request().post(this.currentField.options, value, {
                headers: {'Content-Type': 'application/json'}
            }).then(response => {
                this.uploadButton = value.fileName;
                this.handleData(response, value.fileName);
            });
        },

        // 处理展示的数据和待保存的数据
        handleData(response, fileName) {
            const list = response.data.resources;
            if (typeof list !== 'object' || list.length === 0) {
                const errMsg = response.data.errors ||
                    (response.data.error || (
                        response.data.messages || (
                            response.data.message || '不存在有效数据'
                        )
                    ));
                return this.errorMessage = errMsg;
            }
            const data = response.data.save_data;
            if (typeof data !== 'string' && typeof data !== 'object')
                return this.errorMessage = '返回数据错误，请联系开发人员';

            this.check = this.currentField.saveType === 'data' ? {
                data: data,
                fileName: fileName
            } : {
                file: data,
                fileName: fileName
            };
            this.lists = this.currentField.withIndex ?
                list.map(function (item, i) {item.hxTableIndex = i + 1;return item;}) : list;
        },

        // 保存数据
        fill(formData) {
            formData.append(this.fieldAttribute, JSON.stringify(this.check));
        },
    }
}
</script>
