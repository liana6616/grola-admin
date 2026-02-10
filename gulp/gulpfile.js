// Полностью отключаем deprecation warnings
process.removeAllListeners('warning');


const gulp = require('gulp');
const sass = require('gulp-dart-sass');
const concat = require('gulp-concat');
const autoprefixer = require('gulp-autoprefixer');
const imagemin = require('gulp-imagemin');
const pngquant = require('imagemin-pngquant');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const cleanCSS = require('gulp-clean-css');

// Private project tasks
function js() {
  return gulp.src('../private/resource/js/*.js')
    .pipe(babel({
      presets: ["@babel/preset-env"]
    }))
    .pipe(concat('main.js'))
    .pipe(gulp.dest('../private/src/js'))
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('../private/src/js'));
}

function styles() {
  return gulp.src('../private/resource/scss/*.scss')
    .pipe(sass.sync({
      outputStyle: 'expanded',
      // Добавьте эту строку
      includePaths: ['../private/resource/scss']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      overrideBrowserslist: ['last 4 versions'],
      cascade: false
    }))
    .pipe(gulp.dest('../private/src/css'))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('../private/src/css'));
}

function pstyles() {
  return gulp.src('../public/resource/scss/*.scss')
    .pipe(sass.sync({
      outputStyle: 'expanded',
      // Добавьте эту строку
      includePaths: ['../public/resource/scss']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      overrideBrowserslist: ['last 4 versions'],
      cascade: false
    }))
    .pipe(gulp.dest('../public/src/css'))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('../public/src/css'));
}

// Public project tasks
function pjs() {
  return gulp.src('../public/resource/js/*.js')
    .pipe(babel({
      presets: ["@babel/preset-env"]
    }))
    .pipe(concat('main.js'))
    .pipe(gulp.dest('../public/src/js'))
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('../public/src/js'));
}

// Image compression
function compress() {
  return gulp.src('../public/src/img/1/*')
    .pipe(imagemin({
      interlaced: true,
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use: [pngquant()]
    }))
    .pipe(gulp.dest('../public/src/img/1'));
}

// Watch function
function watchFiles() {
  gulp.watch('../private/resource/scss/**/*.scss', styles);
  gulp.watch('../private/resource/js/**/*.js', js);
  gulp.watch('../public/resource/scss/**/*.scss', pstyles);
  gulp.watch('../public/resource/js/**/*.js', pjs);
}

// Combined tasks
const buildPrivate = gulp.parallel(styles, js);
const buildPublic = gulp.parallel(pstyles, pjs);
const buildAll = gulp.parallel(buildPrivate, buildPublic);

// Export tasks
exports.js = js;
exports.styles = styles;
exports.pjs = pjs;
exports.pstyles = pstyles;
exports.compress = compress;
exports.buildPrivate = buildPrivate;
exports.buildPublic = buildPublic;
exports.buildAll = buildAll;
exports.watch = watchFiles;
exports.default = watchFiles;