const modules = import.meta.glob('./*.vue', { eager: true });

const obj = {};
Object.keys(modules).forEach((key) => {
  obj[key.replace(/(\.\/|\.vue)/g, '')] = modules[key].default;
});
export default obj;
