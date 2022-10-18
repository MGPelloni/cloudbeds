/**
* Gulp: https://gulpjs.com/
* To use this file, run "npm install" and then "npm start" in your terminal at the root of the theme folder.
*/	

const { series, watch, src, dest } = require('gulp'), // Workflow Automation
    concat = require('gulp-concat'), // Concatenate and rename files
    sass = require('gulp-dart-sass'), // Converting our SASS into CSS
    prefix = require('gulp-autoprefixer'), // Prefixes CSS to work with browsers
    cleanCSS = require('gulp-clean-css'), // Minify CSS
    project_name = 'cloudbeds';

function scss() {
  return src(`src/scss/${project_name}.scss`)
  .pipe(concat(`${project_name}.min.css`))
  .pipe(sass())
  .pipe(prefix('last 2 versions'))
  .pipe(cleanCSS({level: {1: {specialComments: 0}}}))
  .pipe(dest('dist'))
}

function monitor() {
  watch('src/scss/**/*.scss', series(scss));
};

exports.default = series(scss, monitor);
exports.compile = series(scss);