const { src, dest, series, parallel, watch} = require("gulp");
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');


function scss() {
    sass.compiler = require('node-sass');
    return src('./src/scss/**/*.scss')
        // .pipe(sourcemaps.init())
        .pipe(sass({
                outputStyle: 'expanded',
                includePaths: ['./node_modules']
            }).on('error', sass.logError))
        .pipe(autoprefixer('>1%','last 2 version','ie >= 11'))
        // .pipe(sourcemaps.write('./'))
        .pipe(dest('./assets/css'));
}


function watcher() {
    watch(['./src/scss/**/*.scss'], scss);
}

exports.scss = scss;
exports.watcher = watcher;
exports.default = scss;
