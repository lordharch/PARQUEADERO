// Importar dependencias
const express = require('express');
const router = express.Router();
const Post = require('../models/Post'); // Importar el modelo Post

// Ruta para obtener todos los posts
router.get('/', async (req, res) => {
  try {
    const posts = await Post.find();
    res.json(posts);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Ruta para crear un nuevo post
router.post('/', async (req, res) => {
  const post = new Post({
    title: req.body.title,
    description: req.body.description
  });

  try {
    const newPost = await post.save();
    res.status(201).json(newPost);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
});

// Ruta para obtener un post por ID
router.get('/:id', async (req, res) => {
  try {
    const post = await Post.findById(req.params.id);
    if (!post) return res.status(404).json({ message: 'Post no encontrado' });
    res.json(post);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Ruta para actualizar un post por ID
router.put('/:id', async (req, res) => {
  try {
    const post = await Post.findByIdAndUpdate(
      req.params.id,
      { title: req.body.title, description: req.body.description },
      { new: true }
    );
    if (!post) return res.status(404).json({ message: 'Post no encontrado' });
    res.json(post);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
});

// Ruta para eliminar un post por ID
router.delete('/:id', async (req, res) => {
  try {
    const post = await Post.findByIdAndDelete(req.params.id);
    if (!post) return res.status(404).json({ message: 'Post no encontrado' });
    res.json({ message: 'Post eliminado correctamente' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Exportar las rutas
module.exports = router;