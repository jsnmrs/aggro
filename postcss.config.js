module.exports = {
  map: {
    inline: false
  },
  plugins: [
    require('autoprefixer')({
      grid: true
    }),
    require('cssnano')
  ]
}
