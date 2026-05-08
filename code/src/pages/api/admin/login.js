export const POST = async ({ request, cookies }) => {
  const { user, pass } = await request.json();
  if (user === 'sistemas' && pass === 'esusistemas123') {
    cookies.set('admin_session', 'active', { path: '/', httpOnly: true, maxAge: 60 * 60 * 24 * 365 });
    return new Response(JSON.stringify({ message: 'Ok' }), { status: 200 });
  }
  return new Response(JSON.stringify({ message: 'Error' }), { status: 401 });
};
