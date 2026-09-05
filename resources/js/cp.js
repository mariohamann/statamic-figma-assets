import FigmaAssets from './pages/FigmaAssets.vue';

Statamic.booting(() => {
  Statamic.$inertia.register('FigmaAssets', FigmaAssets);
});
