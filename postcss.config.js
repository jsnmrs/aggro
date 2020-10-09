module.exports = {
  map: {
    annotation: false,
    inline: false,
  },
  plugins: [
    require("autoprefixer")({
      grid: false,
    }),
    require('cssnano')
  ],
};
