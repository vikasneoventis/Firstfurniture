var config = {
    paths: {
        //'magebuzz/owlcarousel': 'Magebuzz_Timecountdown/js/owl.carousel.min',
        'jquery_plugin': 'Magebuzz_Timecountdown/js/jquery.plugin',
        'simple_style': 'Magebuzz_Timecountdown/js/simple.countdown',
        'colorful_style': 'Magebuzz_Timecountdown/js/colorful.countdown',
        'flip_style': 'Magebuzz_Timecountdown/js/flip.countdown'
    },
    shim: {
        //'magebuzz/owlcarousel': {
        //    deps: ['jquery']
        //},
        'jquery_plugin': {
            deps: ['jquery']
        },
        'simple_style': {
            deps: ['jquery', 'jquery_plugin']
        },
        'colorful_style': {
            deps: ['jquery']
        },
        'flip_style': {
            deps: ['jquery']
        }
    }
};
