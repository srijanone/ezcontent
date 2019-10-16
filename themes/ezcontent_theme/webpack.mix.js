let mix = require('laravel-mix');

mix.sass('scss/style.scss', 'css/');
mix.options({
  processCssUrls: false
});