<template>
<h1>Test</h1>
</template>

<script>
export default {
    data() {
        return {
            tests: 1
        }
    },
    methods: {
        test () {
            console.log('test', this.tests);
        }
    },
    mounted() {
        this.test();
    }
};
</script>