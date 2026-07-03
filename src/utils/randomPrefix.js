module.exports = function randomPrefix() {
  const chars = "abcdefghijklmnopqrstuvwxyz";
  let prefix = "";

  const len = Math.floor(Math.random() * 3) + 1; // 1~3位

  for (let i = 0; i < len; i++) {
    prefix += chars[Math.floor(Math.random() * chars.length)];
  }

  return `$${prefix}`;
};
