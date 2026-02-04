const { src, dest, series, parallel, watch} = require("gulp");
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');


// Compile SCSS
const scss = () => {
  sass.compiler = require('node-sass');
  return src('./assets/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['./node_modules']
    }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(sourcemaps.write('./sourcemap/'))
    .pipe(dest('./assets/css'));
};

exports.default = () => {
  watch(['./assets/scss/**/*.scss'], scss);
};
