var gulp = require('gulp');
var browserify = require('browserify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var eslint = require('gulp-eslint');
var exorcist = require('exorcist');
var browserSync = require('browser-sync');
var watchify = require('watchify');
var babelify = require('babelify');
var uglify = require('gulp-uglify');
var ifElse = require('gulp-if-else');

watchify.args.debug = true;

var sync = browserSync.create();

// Input file.
var bundler = browserify('src/App.js', {
    extensions: ['.js', '.jsx'],
    debug: true
});

// Babel transform
bundler.transform(babelify.configure({
    sourceMapsAbsolute: __dirname + '/src',
    presets: ["@babel/env", "@babel/react"]
}));

// On updates recompile
bundler.on('update', bundle);

function bundle() {
    return bundler.bundle()
        .on('error', function (err) {
            console.log("=====");
            console.error(err.toString());
            console.log("=====");
            this.emit("end");
        })
        .pipe(exorcist('public/js/bundle.js.map'))
        .pipe(source('bundle.js'))
        .pipe(buffer())
        .pipe(ifElse(process.env.NODE_ENV === 'production', uglify))
        //.pipe(uglify())
        .pipe(gulp.dest('public/js'))
    ;
}

gulp.task('lint', () => {
    return gulp.src([
            'src/**/*.jsx',
            'gulpfile.babel.js'
        ])
        .pipe(eslint())
        .pipe(eslint.format())
    ;
});

gulp.task('transpile', gulp.series('lint', () => bundle()));

gulp.task('default', gulp.series('transpile'));





gulp.task('serve', gulp.series('transpile'), () => sync.init({ server: 'public' }));
gulp.task('js-watch', gulp.series('transpile'), () => sync.reload());

gulp.task('watch-op', () => {
    gulp.watch('src/**/*.js', gulp.series('transpile'))
})

gulp.task('watch', gulp.series('serve', () => {
    gulp.watch(['src/**/*.js'], gulp.series('js-watch'));
    gulp.watch('public/assets/style.css', sync.reload);
    gulp.watch('public/index.html', sync.reload);
}));