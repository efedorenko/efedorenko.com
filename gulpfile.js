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
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var md5 = require("gulp-md5-assets");
var rename = require('gulp-rename');
// var include = require("gulp-include");


// Stylesheets

gulp.task('styles:sass', function () {
  return gulp.src(paths.styles.src + '**/*.scss')
    .pipe(sass({
      precision: 5
    }))
    .pipe(postcss([
      autoprefixer({
        browsers: ['last 2 versions', 'ie >= 9', 'ios >= 8']
      }),
/*
      cssnano({
        safe: true,
        autoprefixer: false,       // Run on its own
        discardDuplicates: false   // Extremely slow
      })
*/
    ]))
    .pipe(gulp.dest(paths.styles.dist));
});


// Scripts

gulp.task('scripts:sync', function () {
  return gulp.src([
    paths.scripts.src + 'vendor/jquery-2.2.2.min.js',
    paths.scripts.src + 'vendor/modernizr.js',
    paths.scripts.src + 'vendor/prism.min.js'
  ])
    .pipe(gulp.dest(paths.scripts.dist));
});

gulp.task('scripts:fonts', function () {
  return gulp.src([
    paths.scripts.src + 'vendor/cookie.js',
    paths.scripts.src + 'vendor/fontfaceobserver-1.7.1.js',

    // Modules
    paths.scripts.src + 'font-loader.js'
  ])
    .pipe(concat('fonts.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(paths.scripts.dist));
});

gulp.task('scripts:landing', function () {
  return gulp.src([
    // Vendor plugins
    paths.scripts.src + 'vendor/jquery.magnific-popup.js',
    paths.scripts.src + 'vendor/slick.js',

    // Modules
    paths.scripts.src + 'landing/carousel.js',
    paths.scripts.src + 'landing/popups.js',
    paths.scripts.src + 'landing/timezones.js',
    paths.scripts.src + 'landing/decors.js'
  ])
    .pipe(concat('landing.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(paths.scripts.dist));
});

gulp.task('scripts:jobs', function () {
  return gulp.src([
    paths.scripts.src + 'jobs/jobs.js'
  ])
    .pipe(concat('jobs.min.js'))
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
  gulp.watch(paths.styles.src   + '**/*.scss', ['styles:sass', 'cachebust:images']);

  gulp.watch(paths.elements.src + '**/*.scss', ['styles:sass', 'cachebust:images']);

  gulp.watch(paths.images.dist  + '**/*', ['cachebust:images']);

  gulp.watch(paths.scripts.src  + 'vendor/**/*.js', ['scripts']);
  gulp.watch(paths.scripts.src  + '*.js', ['scripts:fonts']);
  gulp.watch(paths.scripts.src  + 'landing/**/*.js', ['scripts:landing']);
  gulp.watch(paths.scripts.src  + 'jobs/**/*.js', ['scripts:jobs']);
});

gulp.task('default', ['styles:sass', 'images:sync', 'cachebust:images', 'scripts:sync', 'scripts:landing', 'scripts:fonts', 'scripts:jobs']);

gulp.task('production', ['default', 'cachebust:css', 'cachebust:js']);
