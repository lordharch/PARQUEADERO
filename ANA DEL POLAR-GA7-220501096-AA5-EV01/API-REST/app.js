const express = require('express');
const mongoose = require('mongoose');
const bodyParser = require('body-parser');
const app = express();

app.use(bodyParser.json()); // Para recibir JSON

// Conexión a la BD
mongoose.connect(
  'mongodb+srv://admin:EO7qmwI1pH0iIUQb@cluster0.dziylbz.mongodb.net/parqueadero?retryWrites=true&w=majority&appName=Cluster0',
  { useNewUrlParser: true, useUnifiedTopology: true },
  () => {
    console.log('Si hay conexión a la BD');
  }
);

/* SE CREAN LAS RUTAS */
app.get('/', (req, res) => {
  res.json({ mensaje: 'prueba 1 respuesta del servidor' }); // Responde en JSON
});

const postRoutes = require('./routes/post');
app.use('/posts', postRoutes);

app.listen(10000);