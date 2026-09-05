<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { router, usePage } from '@statamic/cms/inertia';
import { Alert, Badge, Button, Card, Header } from '@statamic/cms/ui';

defineProps({
    configs: {
        type: Array,
        default: () => [],
    },
});

const activeConfig = ref(null);
const progressMessage = ref('');
let progressTimer;

const flash = computed(() => usePage().props.flash ?? {});

function startProgressPolling(progressUrl) {
    window.clearInterval(progressTimer);

    const poll = async () => {
        try {
            const response = await fetch(progressUrl);
            const data = await response.json();
            progressMessage.value = data.message ?? 'Processing...';
        } catch {
            progressMessage.value = 'Processing...';
        }
    };

    poll();
    progressTimer = window.setInterval(poll, 1500);
}

function runAction(config, action) {
    if (action === 'reimport' && !window.confirm('Reimport all assets and overwrite existing files?')) {
        return;
    }

    activeConfig.value = config;
    progressMessage.value = action === 'info' ? 'Loading...' : 'Preparing import...';

    if (action !== 'info') {
        startProgressPolling(config.actions.progress);
    }

    router.post(config.actions[action], {}, {
        preserveScroll: true,
        onFinish: () => {
            window.clearInterval(progressTimer);
            activeConfig.value = null;
            progressMessage.value = '';
        },
    });
}

onBeforeUnmount(() => window.clearInterval(progressTimer));
</script>

<template>
    <Header title="Figma Assets" />

    <Alert v-if="flash.success" variant="success" class="mb-4">
        {{ flash.success }}
    </Alert>

    <Alert v-if="flash.error" variant="error" class="mb-4">
        {{ flash.error }}
    </Alert>

    <Card>
        <div class="figma-assets-table-wrapper">
            <table class="figma-assets-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Assets Container</th>
                        <th>Figma Page</th>
                        <th>Figma Frame</th>
                        <th>Format</th>
                        <th>Scale</th>
                        <th class="figma-assets-actions-heading">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="config in configs" :key="config.actions.import">
                        <td>{{ config.title || 'Untitled configuration' }}</td>
                        <td><Badge>{{ config.assets_container || 'Missing' }}</Badge></td>
                        <td><Badge>{{ config.page_title || 'Missing' }}</Badge></td>
                        <td><Badge v-if="config.frame_title">{{ config.frame_title }}</Badge></td>
                        <td><Badge>{{ config.format }}</Badge></td>
                        <td><Badge>{{ config.scale }}</Badge></td>
                        <td class="figma-assets-actions">
                            <span v-if="activeConfig === config">{{ progressMessage }}</span>
                            <template v-else>
                                <Button size="sm" @click="runAction(config, 'info')">Info</Button>
                                <Button size="sm" @click="runAction(config, 'reimport')">Reimport</Button>
                                <Button size="sm" variant="primary" @click="runAction(config, 'import')">Import</Button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="configs.length === 0">
                        <td colspan="7">No Figma configurations are available.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </Card>
</template>
