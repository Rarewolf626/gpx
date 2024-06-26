const path = require('node:path'),
    gulp = require('gulp'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify');


exports.default = function () {
    return gulp.src([
        path.join(__dirname, 'src/js/helpers/*.js'),
        path.join(__dirname, 'src/js/*.js'),
    ])
        .pipe(concat('custom.js'))
        .pipe(uglify())
        .pipe(gulp.dest(path.join(__dirname, 'build/js')));
}
