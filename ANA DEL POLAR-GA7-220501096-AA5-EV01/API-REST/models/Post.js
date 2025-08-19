const mongoose = require('mongoose');

// Definir el esquema para "Post"
const PostSchema = new mongoose.Schema({
  title: {
    type: String,
    required: true // El campo es obligatorio
  },
  description: {
    type: String,
    required: true // El campo es obligatorio
  },
  date: {
    type: Date,
    default: Date.now // Valor por defecto: fecha actual
  }
});

// Exportar el modelo para poder usarlo en otros archivos
module.exports = mongoose.model('Post', PostSchema);