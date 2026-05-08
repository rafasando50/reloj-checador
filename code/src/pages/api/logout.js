export const POST = async ({ cookies }) => {
  cookies.delete('session', { path: '/' });
  cookies.delete('admin_session', { path: '/' });
  return new Response(JSON.stringify({ message: 'Ok' }), { status: 200 });
};
