'use strict';

var paths = {
  templates: {
    dist: './craft/templates/'
  },
  elements: {
    src: './craft/templates/elements/'
  },
  scripts: {
    src:  './html/src/javascripts/',
    dist: './html/javascripts/'
  },
  styles: {
    src:  './html/src/stylesheets/',
    dist: './html/stylesheets/'
  },
  images: {
    dist: './html/images/'
  }
};

var gulp = require('gulp');
var sass = require('gulp-sass');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');
var cssnano = require('cssnano');
var babel = require('gulp-babel');
var uglify = require('gulp-uglify');
var md5 = require("gulp-md5-assets");
var rename = require('gulp-rename');
var browserify = require('browserify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');


// Stylesheets

gulp.task('styles:sass', function () {
  return gulp.src(paths.styles.src + '**/*.scss')
    .pipe(sass({
      precision: 5
    }))
    .pipe(postcss([
      autoprefixer({
        browsers: ['last 2 versions', 'ie >= 10', 'ios >= 8']
      }),
      cssnano({
        safe: true,
        autoprefixer: false,       // Run on its own
        discardDuplicates: false   // Extremely slow
      })
    ]))
    .pipe(gulp.dest(paths.styles.dist));
});


// Scripts

gulp.task('scripts:sync', function () {
  return gulp.src([
    paths.scripts.src + 'prism.min.js'
  ])
    .pipe(gulp.dest(paths.scripts.dist));
});
gulp.task('scripts:fonts', function () {
  return browserify(paths.elements.src + 'components/fonts-check/fonts-loader.js')
    .bundle()
    .pipe(source('fonts.min.js'))
    .pipe(buffer())
    .pipe(babel({presets: ['es2015']}))
    .pipe(uglify())
    .pipe(gulp.dest(paths.scripts.dist));
});


// Move images

gulp.task('images:sync', function () {
  return gulp.src([
    paths.elements.src + '**/*.jpg',
    paths.elements.src + '**/*.png',
    paths.elements.src + '**/*.gif',
    paths.elements.src + '**/*.svg'
  ])
    .pipe(rename({dirname: ''}))
    .pipe(gulp.dest(paths.images.dist));
});


// Bust cache

gulp.task('cachebust:images', ['styles:sass'], function () {
  return gulp.src(paths.images.dist + '**/*')
    .pipe(md5(10, paths.styles.dist + '**/*.css'));
});

gulp.task('cachebust:css', ['default'], function () {
  return gulp.src(paths.styles.dist + '**/*.css', {base: './html/'})
    .pipe(md5(10, paths.templates.dist + '**/*.twig'));
});

gulp.task('cachebust:js', ['default'], function () {
  return gulp.src(paths.scripts.dist + '**/*.js', {base: './html/'})
    .pipe(md5(10, paths.templates.dist + '**/*.twig'));
});


// Watch & Default

gulp.task('watch', function () {
  gulp.watch([
    paths.elements.src + '**/*.scss',
    paths.styles.src   + '**/*.scss'
  ], [
    'styles:sass',
    'cachebust:images'
  ]);

  gulp.watch([
    paths.elements.src + '**/*.jpg',
    paths.elements.src + '**/*.png',
    paths.elements.src + '**/*.gif',
    paths.elements.src + '**/*.svg',
  ], [
    'images:sync'
  ]);

  gulp.watch([
    paths.images.dist  + '**/*'
  ], [
    'cachebust:images'
  ]);

  gulp.watch([
    paths.elements.src + '**/*.js',
    paths.scripts.src  + '**/*.js'
  ], [
    'scripts:fonts'
  ]);
});

gulp.task('default', ['styles:sass', 'scripts:sync', 'scripts:fonts', 'images:sync', 'cachebust:images']);
gulp.task('production', ['default', 'cachebust:css', 'cachebust:js']);
